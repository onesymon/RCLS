<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/config.php');
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
  header("Location: /rotary/webpages/logout/logout.php");
  exit();
}

$isAdmin = in_array($_SESSION['role'], [1, 3, 4, 100]);

function getMemberName($conn, $id) {
  $stmt = $conn->prepare("SELECT fullname FROM members WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();
  return $result ? $result['fullname'] : 'Unknown';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $msg = trim($_POST['new_announcement'] ?? '');
  $title = trim($_POST['announcement_title'] ?? 'Announcement');
  $editId = $_POST['edit_id'] ?? null;

  if (!empty($msg)) {
    if ($editId) {
      $stmt = $conn->prepare("UPDATE club_announcements SET title = ?, message = ? WHERE id = ?");
      $stmt->bind_param("ssi", $title, $msg, $editId);
    } else {
      $stmt = $conn->prepare("INSERT INTO club_announcements (title, message, encoded_by) VALUES (?, ?, ?)");
      $stmt->bind_param("ssi", $title, $msg, $_SESSION['user_id']);
    }
    $stmt->execute();
  }

  if (isset($_POST['delete_announcement'])) {
    $stmt = $conn->prepare("DELETE FROM club_announcements WHERE id = ?");
    $stmt->bind_param("i", $_POST['delete_announcement']);
    $stmt->execute();
  }

  if (isset($_POST['toggle_pin'])) {
    $id = $_POST['toggle_pin'];
    $stmt = $conn->prepare("UPDATE club_announcements SET is_pinned = NOT is_pinned WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
  }

  header("Location: announcements.php");
  exit();
}

$announcementQuery = "SELECT * FROM club_announcements ORDER BY is_pinned DESC, encoded_at DESC";
$announcements = $conn->query($announcementQuery)->fetch_all(MYSQLI_ASSOC);

$projectsQuery = "
  SELECT id, title, description, start_date AS date, end_date, location, 'Project' AS type, status
  FROM club_projects
  WHERE status IN ('Upcoming', 'Ongoing')
  ORDER BY start_date ASC";
$eventsQuery = "
  SELECT id, title, description, event_date AS date, event_time AS end_date, location, 'Event' AS type, status
  FROM club_events
  WHERE status IN ('Upcoming', 'Ongoing')
  ORDER BY event_date ASC";

$projects = $conn->query($projectsQuery)->fetch_all(MYSQLI_ASSOC);
$events = $conn->query($eventsQuery)->fetch_all(MYSQLI_ASSOC);
$allItems = array_merge($projects, $events);
usort($allItems, fn($a, $b) => strtotime($a['date']) - strtotime($b['date']));

foreach ($allItems as &$item) {
  $folder = $item['type'] === 'Project' ? 'club_projects' : 'club_events';
  $imagePath = "/rotary/uploads/$folder/{$item['id']}.jpg";
  $item['image'] = file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath) ? $imagePath : "/rotary/uploads/$folder/default.jpg";
}
unset($item);

$calendarEvents = [];
foreach ($allItems as $item) {
  $baseProps = [
    'id' => $item['id'],
    'type' => $item['type'],
    'status' => $item['status'],
    'location' => $item['location'],
    'description' => $item['description'] ?: 'No additional details.',
    'image' => $item['image'],
    'title' => $item['title'],
    'date' => $item['date'],
    'end_date' => $item['end_date'] ?? $item['date']
  ];

  $calendarEvents[] = [
    'title' => $item['type'] . ': ' . $item['title'] . ' (Start)',
    'start' => $item['date'],
    'color' => '#4caf50',
    'textColor' => 'white',
    'displayOrder' => 1,
    'extendedProps' => $baseProps
  ];

  if (!empty($item['end_date']) && $item['end_date'] !== $item['date']) {
    $calendarEvents[] = [
      'title' => $item['type'] . ': ' . $item['title'] . ' (End)',
      'start' => $item['end_date'],
      'color' => '#f44336',
      'textColor' => 'white',
      'displayOrder' => 2,
      'extendedProps' => $baseProps
    ];
  }
}
?>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/header.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<link rel="stylesheet" href="/rotary/announcements/style.css?v=<?= time() ?>">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed announcements-page">
<div class="wrapper">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/nav.php'); ?>
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/sidebar.php'); ?>

  <div class="content-wrapper">
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/page_title.php'); ?>

    <section class="content py-4">
      <div class="container-fluid px-3">
        <div class="d-flex flex-wrap flex-lg-nowrap gap-4 mb-4">
          <!-- Project/Event Section -->
          <div class="section-card flex-fill">
            <div class="section-banner"><h5>📅 Projects & Events Overview</h5></div>
            <div class="badge-legend my-3">
              <span class="badge badge-success">Ongoing</span>
              <span class="badge badge-primary">Upcoming</span>
              <span class="badge badge-secondary">Project</span>
              <span class="badge badge-warning">Event</span>
            </div>
            <div class="cards-grid pe-3">
              <?php foreach ($allItems as $item): ?>
                <?php
                  $isProject = $item['type'] === 'Project';
                  $folder = $isProject ? 'club_projects' : 'club_events';
                  $imagePath = "/rotary/uploads/$folder/{$item['id']}.jpg";
                  $image = file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)
                    ? $imagePath
                    : "/rotary/uploads/$folder/default.jpg";
                  $status = ucfirst(strtolower($item['status']));
                  $badgeClass = $status === 'Ongoing' ? 'badge-success' : 'badge-primary';
                ?>
                <div class="card-box <?= $isProject ? 'project-card' : 'event-card' ?>"
                     data-details='<?= json_encode([
                       'id' => $item['id'],
                       'type' => $item['type'],
                       'title' => $item['title'],
                       'status' => $status,
                       'date' => date("F d, Y", strtotime($item['date'])),
                       'end_date' => isset($item['end_date']) ? date("F d, Y", strtotime($item['end_date'])) : 'N/A',
                       'location' => $item['location'],
                       'description' => $item['description'] ?: 'No additional details provided.',
                       'image' => $image
                     ]) ?>'
                     data-bs-toggle="modal" data-bs-target="#detailsModal">
                  <div class="card-img" style="background-image: url('<?= $image ?>');">
                    <div class="overlay <?= $isProject ? 'overlay-project' : 'overlay-event' ?>"></div>
                  </div>
                  <div class="card-body">
                    <div class="card-tags">
                      <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
                      <span class="badge <?= $isProject ? 'badge-project' : 'badge-event' ?>"><?= $item['type'] ?></span>
                    </div>
                    <h5><?= htmlspecialchars($item['title']) ?></h5>
                    <p><?= $isProject ? "Starts: " : "Scheduled: " ?><?= date("F d, Y", strtotime($item['date'])) ?></p>
                    <p class="location"><i class="fas fa-map-marker-alt"></i> <?= $item['location'] ?></p>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Announcements Panel -->
          <div class="section-card right-panel" style="min-width: 300px; max-width: 360px;">
            <div class="section-banner d-flex justify-content-between align-items-center">
              <h5>📢 Rotary Announcements</h5>
              <?php if ($isAdmin): ?>
                <button class="add-announcement-btn" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                  <i class="fas fa-plus"></i>
                </button>
              <?php endif; ?>
            </div>
              <div class="announcement-content show" id="announcement-content">
                <?php
                  $pinned = array_filter($announcements, fn($a) => $a['is_pinned'] == 1);
                  $unpinned = array_filter($announcements, fn($a) => $a['is_pinned'] == 0);
                ?>

                <?php if (count($pinned) > 0): ?>
                  <div class="announcement-divider"><i class="fas fa-thumbtack"></i> Pinned Announcements</div>
                  <?php foreach (array_slice($pinned, 0, 2) as $a): include('announcement_card.php'); endforeach; ?>
                <?php endif; ?>

                <?php if (count($unpinned) > 0): ?>
                  <?php if (count($pinned) > 0): ?>
                    <div class="announcement-divider"><i class="fas fa-file-alt"></i> Other Announcements</div>
                  <?php endif; ?>
                  <?php foreach (array_slice($unpinned, 0, 3) as $a): include('announcement_card.php'); endforeach; ?>
                <?php endif; ?>

                <?php if (count($pinned) + count($unpinned) > 5): ?>
                  <div class="text-center mt-2">
                    <button class="btn btn-outline-primary rounded-pill px-4 py-1 show-more-btn" data-bs-toggle="modal" data-bs-target="#allAnnouncementsModal">
                      <i class="fas fa-arrow-down me-1"></i> Show More Announcements
                    </button>
                  </div>
                <?php endif; ?>
              </div>
          </div>
        </div>

        <!-- Calendar Section -->
        <div class="section-card">
          <div class="section-banner"><h5>🗓️ Calendar Overview</h5></div>
          <div class="calendar-container px-2 pt-3"><div id="calendar"></div></div>
        </div>
      </div>
    </section>
  </div>

  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline-block">
      <b>Developed By</b> <a href="#">Group 9</a>
    </div>
  </footer>
</div>

<!-- Modal: Details -->
<div class="modal fade" id="detailsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow rounded-4 border-0 animate-fade-in">
      <div class="modal-header bg-primary text-white rounded-top">
        <h5 class="modal-title" id="detailsModalLabel"></h5>
      </div>
      <div class="modal-body row g-4 p-4 align-items-start">
        <!-- Left Side: Image + Upload -->
        <div class="col-md-5">
          <img id="modalImage" class="img-fluid rounded shadow-sm mb-3 border border-light" alt="Preview">
          <form id="thumbnailForm" method="POST" enctype="multipart/form-data" action="/rotary/announcements/upload_thumbnail.php">
            <input type="hidden" name="id" id="modalImageId">
            <input type="hidden" name="type" id="modalImageType">
            <input type="file" name="thumbnail" accept="image/jpeg,image/png" class="form-control mb-2" required>
            <button class="btn btn-primary btn-sm w-100">
              <i class="fas fa-upload me-1"></i> Upload New Thumbnail
            </button>
          </form>
        </div>

        <!-- Right Side: Details -->
        <div class="col-md-7">
          <h4 id="modalTitle" class="fw-bold text-dark mb-3"></h4>

          <div class="detail-section mb-3">
            <p><i class="fas fa-tag me-2 text-info"></i><strong> Type:</strong> <span id="modalType"></span></p>
            <p><i class="fas fa-bullhorn me-2 text-success"></i><strong> Status:</strong> <span id="modalStatus"></span></p>
          </div>

          <div class="detail-section mb-3">
            <h6 class="text-primary mb-2">📅 Schedule</h6>
            <p><i class="fas fa-calendar-plus me-2 text-secondary"></i><strong> Start:</strong> <span id="modalDate"></span></p>
            <p><i class="fas fa-calendar-check me-2 text-secondary"></i><strong> End:</strong> <span id="modalEndDate"></span></p>
          </div>

          <div class="detail-section mb-3">
            <p><i class="fas fa-map-marker-alt me-2 text-danger"></i><strong> Location:</strong> <span id="modalLocation"></span></p>
          </div>

          <div class="detail-section">
            <h6 class="text-primary mb-2">📝 Description</h6>
            <p id="modalDescription" class="text-muted small mb-0"> No description provided.</p>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer justify-content-end bg-light border-top-0 px-4 pb-4">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Upload Success -->
<div class="modal fade" id="uploadSuccessModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Thumbnail Updated</h5>
      </div>
      <div class="modal-body">The thumbnail image was successfully updated.</div>
    </div>
  </div>
</div>

<!-- Modal: Add/Edit Announcement -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered mx-auto"> <!-- Centered horizontally and vertically -->
    <div class="modal-content border-primary">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addAnnouncementLabel">Add New Announcement</h5>
        <!-- No close button -->
      </div>
      <form method="POST">
        <input type="hidden" name="edit_id" id="edit_id">
        <div class="modal-body">
          <input type="text" name="announcement_title" id="announcement_title" class="form-control mb-3" placeholder="Title" required>
          <textarea class="form-control" name="new_announcement" id="new_announcement" rows="4" placeholder="Message" required></textarea>
        </div>
        <div class="modal-footer justify-content-end gap-2">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-success" id="submitAnnouncementBtn" type="submit">Post Announcement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: All Announcements -->
<div class="modal fade" id="allAnnouncementsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-primary">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">📢 All Announcements</h5>
      </div>
      <div class="modal-body">
        <!-- Pinned Section -->
        <div id="pinnedAnnouncementsSection" class="modal-announcements-section">
          <h6 class="text-primary d-flex justify-content-between align-items-center">
            <span><i class="fas fa-thumbtack me-1"></i> Pinned Announcements</span>
            <input type="text" class="form-control form-control-sm w-50" placeholder="Search..." id="pinnedAnnouncementsContainerSearch">
          </h6>
          <div id="pinnedAnnouncementsContainer"></div>
          <ul class="pagination pagination-sm justify-content-center mt-3" id="pinnedPagination"></ul>
        </div>

        <hr class="my-4">

        <!-- Other Section -->
        <div id="otherAnnouncementsSection" class="modal-announcements-section">
          <h6 class="text-secondary d-flex justify-content-between align-items-center">
            <span><i class="fas fa-file-alt me-1"></i> Other Announcements</span>
            <input type="text" class="form-control form-control-sm w-50" placeholder="Search..." id="otherAnnouncementsContainerSearch">
          </h6>
          <div id="otherAnnouncementsContainer"></div>
          <ul class="pagination pagination-sm justify-content-center mt-3" id="otherPagination"></ul>
        </div>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const pinnedData = <?= json_encode(array_values($pinned)) ?>.sort((a, b) => new Date(b.encoded_at) - new Date(a.encoded_at));
  const otherData = <?= json_encode(array_values($unpinned)) ?>.sort((a, b) => new Date(b.encoded_at) - new Date(a.encoded_at));

function formatReadableDate(dateString) {
  const date = new Date(dateString);

  const optionsDate = {
    year: 'numeric',
    month: 'long',
    day: '2-digit'
  };
  const optionsTime = {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  };

  const formattedDate = date.toLocaleDateString('en-US', optionsDate);
  const formattedTime = date.toLocaleTimeString('en-US', optionsTime);

  return `${formattedDate} | ${formattedTime}`;
}

  const renderPaginated = (data, containerId, paginationId, perPage, type) => {
    const container = document.getElementById(containerId);
    const pagination = document.getElementById(paginationId);
    const searchInput = document.getElementById(`${containerId}Search`);
    let currentPage = 1;
    let filteredData = [...data];

    const renderPage = () => {
      container.innerHTML = '';
      const start = (currentPage - 1) * perPage;
      const items = filteredData.slice(start, start + perPage);

      if (items.length === 0) {
        container.innerHTML = `<div class="announcement-card">No ${type} announcements found.</div>`;
        pagination.innerHTML = '';
        return;
      }

      items.forEach(item => {
        const div = document.createElement('div');
        div.className = 'announcement-card';
        div.innerHTML = `
          <div class="announcement-options">
            <i class="fas fa-thumbtack text-${item.is_pinned ? 'warning' : 'muted'}"></i>
          </div>
          <h6 class="mb-1">${item.title}</h6>
          <p class="mb-1 small text-muted">${formatReadableDate(item.encoded_at)}</p>
          <p class="mb-0">${item.message}</p>
        `;
        container.appendChild(div);
      });

      pagination.innerHTML = '';
      const totalPages = Math.ceil(filteredData.length / perPage);
      for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = 'page-item' + (i === currentPage ? ' active' : '');
        li.innerHTML = `<button class="page-link">${i}</button>`;
        li.addEventListener('click', () => {
          currentPage = i;
          renderPage();
        });
        pagination.appendChild(li);
      }
    };

    searchInput.addEventListener('input', () => {
      const keyword = searchInput.value.toLowerCase();
      filteredData = data.filter(a => a.title.toLowerCase().includes(keyword) || a.message.toLowerCase().includes(keyword));
      currentPage = 1;
      renderPage();
    });

    renderPage();
  };

  document.getElementById('allAnnouncementsModal').addEventListener('show.bs.modal', () => {
    renderPaginated(pinnedData, 'pinnedAnnouncementsContainer', 'pinnedPagination', 2, 'pinned');
    renderPaginated(otherData, 'otherAnnouncementsContainer', 'otherPagination', 3, 'other');
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const detailsModalEl = document.getElementById('detailsModal');
  const detailsModal = new bootstrap.Modal(detailsModalEl);
  const thumbnailInput = document.querySelector('input[name="thumbnail"]');
  const modalImage = document.getElementById('modalImage');

  const formatDate = (str) => {
    return new Date(str).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  function setModalDetails(d) {
    document.getElementById('detailsModalLabel').textContent = `${d.type}: ${d.title} Details`;
    document.getElementById('modalTitle').textContent = `${d.type}: ${d.title}`;
    document.getElementById('modalType').textContent = d.type;
    document.getElementById('modalStatus').textContent = d.status;
    document.getElementById('modalDate').textContent = formatDate(d.date);
    document.getElementById('modalEndDate').textContent = formatDate(d.end_date);
    document.getElementById('modalLocation').textContent = d.location;
    document.getElementById('modalDescription').textContent = d.description;
    modalImage.src = d.image;
    document.getElementById('modalImageId').value = d.id;
    document.getElementById('modalImageType').value = d.type;

    if (thumbnailInput) {
      thumbnailInput.value = '';
      thumbnailInput.dataset.original = d.image;
    }
  }

  document.querySelectorAll('.card-box').forEach(card => {
    card.addEventListener('click', () => {
      const d = JSON.parse(card.dataset.details);
      setModalDetails(d);
      detailsModal.show();
    });
  });

  const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    height: 'auto',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: ''
    },
    eventOrder: 'displayOrder',
    events: <?= json_encode($calendarEvents) ?>,
    eventClick: function (info) {
      const d = info.event.extendedProps;
      setModalDetails(d);
      detailsModal.show();
    }
  });

  calendar.render();

  if (thumbnailInput) {
    thumbnailInput.addEventListener('change', function () {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          modalImage.src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // Universal cancel buttons
  document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {
    btn.addEventListener('click', () => {
      detailsModal.hide();

      // Remove modal backdrop manually if lingering
      document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());

      if (thumbnailInput) {
        thumbnailInput.value = '';
      }

      if (modalImage && thumbnailInput?.dataset.original) {
        modalImage.src = thumbnailInput.dataset.original;
      }
    });
  });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.edit-announcement').forEach(button => {
    button.addEventListener('click', () => {
      const id = button.getAttribute('data-id');
      const title = button.getAttribute('data-title');
      const message = button.getAttribute('data-message');

      // Fill modal form inputs
      document.getElementById('edit_id').value = id;
      document.getElementById('announcement_title').value = title;
      document.getElementById('new_announcement').value = message;

      // Change modal title & button label
      document.getElementById('addAnnouncementLabel').textContent = 'Edit Announcement';
      document.getElementById('submitAnnouncementBtn').textContent = 'Update Announcement';
    });
  });

  // Optional: Reset modal on close
  const addModal = document.getElementById('addAnnouncementModal');
  addModal.addEventListener('hidden.bs.modal', () => {
    document.getElementById('edit_id').value = '';
    document.getElementById('announcement_title').value = '';
    document.getElementById('new_announcement').value = '';
    document.getElementById('addAnnouncementLabel').textContent = 'Add New Announcement';
    document.getElementById('submitAnnouncementBtn').textContent = 'Post Announcement';
  });
});
</script>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/footer.php'); ?>
</body>
</html>