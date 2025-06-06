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
?>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/header.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<link rel="stylesheet" href="/rotary/announcements/style.css?v=<?= time() ?>">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
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
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">+</button>
              <?php endif; ?>
            </div>
            <div class="announcement-content show" id="announcement-content">
              <?php $pinned = array_filter($announcements, fn($a) => $a['is_pinned'] == 1); ?>
              <?php if (count($pinned) > 0): ?>
                <div class="announcement-divider"><i class="fas fa-thumbtack"></i> Pinned Announcements</div>
                <?php foreach ($pinned as $a): include('announcement_card.php'); endforeach; ?>
              <?php endif; ?>

              <?php $unpinned = array_filter($announcements, fn($a) => $a['is_pinned'] == 0); ?>
              <?php if (count($unpinned) > 0): ?>
                <?php if (count($pinned) > 0): ?>
                  <div class="announcement-divider"><i class="fas fa-file-alt"></i> Other Announcements</div>
                <?php endif; ?>
                <?php foreach ($unpinned as $a): include('announcement_card.php'); endforeach; ?>
              <?php else: ?>
                <?php if (count($pinned) === 0): ?>
                  <div class="announcement-card">No announcements yet.</div>
                <?php endif; ?>
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
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="detailsModalLabel">Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row">
        <div class="col-md-5">
          <img id="modalImage" class="img-fluid rounded mb-3 w-100" alt="Preview">
          <form id="thumbnailForm" method="POST" enctype="multipart/form-data" action="/rotary/announcements/upload_thumbnail.php">
            <input type="hidden" name="id" id="modalImageId">
            <input type="hidden" name="type" id="modalImageType">
            <input type="file" name="thumbnail" accept="image/jpeg,image/png" class="form-control mb-2" required>
            <button class="btn btn-primary btn-sm w-100">Upload New Thumbnail</button>
          </form>
        </div>
        <div class="col-md-7">
          <h4 id="modalTitle"></h4>
          <p><strong>Type:</strong> <span id="modalType"></span></p>
          <p><strong>Status:</strong> <span id="modalStatus"></span></p>
          <p><strong>Start Date:</strong> <span id="modalDate"></span></p>
          <p><strong>End Date:</strong> <span id="modalEndDate"></span></p>
          <p><strong>Location:</strong> <span id="modalLocation"></span></p>
          <p><strong>Description:</strong></p>
          <p id="modalDescription"></p>
        </div>
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
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">The thumbnail image was successfully updated.</div>
    </div>
  </div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    events: <?= json_encode(array_map(fn($item) => [
      'title' => $item['type'] . ': ' . $item['title'],
      'start' => $item['date']
    ], $allItems)) ?>
  });
  calendar.render();

  document.querySelectorAll('.card-box').forEach(card => {
    card.addEventListener('click', () => {
      const d = JSON.parse(card.dataset.details);
      document.getElementById('modalTitle').textContent = d.title;
      document.getElementById('modalType').textContent = d.type;
      document.getElementById('modalStatus').textContent = d.status;
      document.getElementById('modalDate').textContent = d.date;
      document.getElementById('modalEndDate').textContent = d.end_date;
      document.getElementById('modalLocation').textContent = d.location;
      document.getElementById('modalDescription').textContent = d.description;
      document.getElementById('modalImage').src = d.image;
      document.getElementById('modalImageId').value = d.id;
      document.getElementById('modalImageType').value = d.type;
    });
  });

  if (new URLSearchParams(window.location.search).get("uploaded") === "1") {
    new bootstrap.Modal(document.getElementById('uploadSuccessModal')).show();
    history.replaceState({}, document.title, window.location.pathname);
  }

  document.querySelectorAll('.edit-announcement').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('edit_id').value = btn.dataset.id;
      document.getElementById('announcement_title').value = btn.dataset.title;
      document.getElementById('new_announcement').value = btn.dataset.message;
      document.getElementById('addAnnouncementLabel').textContent = 'Edit Announcement';
      document.getElementById('submitAnnouncementBtn').textContent = 'Update Announcement';
    });
  });

  const addBtn = document.querySelector('[data-bs-target="#addAnnouncementModal"]');
  if (addBtn) {
    addBtn.addEventListener('click', () => {
      document.getElementById('edit_id').value = '';
      document.getElementById('announcement_title').value = '';
      document.getElementById('new_announcement').value = '';
      document.getElementById('addAnnouncementLabel').textContent = 'Add New Announcement';
      document.getElementById('submitAnnouncementBtn').textContent = 'Post Announcement';
    });
  }
});
</script>

<!-- Modal: Add/Edit Announcement -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-primary">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addAnnouncementLabel">Add New Announcement</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="edit_id" id="edit_id">
        <div class="modal-body">
          <input type="text" name="announcement_title" id="announcement_title" class="form-control mb-3" placeholder="Title" required>
          <textarea class="form-control" name="new_announcement" id="new_announcement" rows="4" placeholder="Message" required></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary w-100" id="submitAnnouncementBtn" type="submit">Post Announcement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/rotary/includes/footer.php'); ?>
</body>
</html>
