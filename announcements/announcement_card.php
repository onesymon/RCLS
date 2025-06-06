<div class="announcement-card position-relative">
  <div class="dropdown announcement-options">
    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="fas fa-ellipsis-v"></i>
    </button>
    <ul class="dropdown-menu shadow-sm dropdown-menu-end p-2">
      <li>
        <button type="button"
          class="btn btn-info btn-sm w-100 edit-announcement"
          data-id="<?= $a['id'] ?>"
          data-title="<?= htmlspecialchars($a['title'], ENT_QUOTES) ?>"
          data-message="<?= htmlspecialchars($a['message'], ENT_QUOTES) ?>"
          data-bs-toggle="modal"
          data-bs-target="#addAnnouncementModal">
          <i class="fas fa-edit"></i> Edit
        </button>
      </li>
      <li class="mt-2">
        <form method="POST">
          <input type="hidden" name="toggle_pin" value="<?= $a['id'] ?>">
          <button type="submit" class="btn btn-warning btn-sm w-100">
            <i class="fas fa-thumbtack"></i> <?= $a['is_pinned'] ? 'Unpin' : 'Pin' ?>
          </button>
        </form>
      </li>
      <li class="mt-2">
        <form method="POST">
          <input type="hidden" name="delete_announcement" value="<?= $a['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm w-100">
            <i class="fas fa-trash-alt"></i> Delete
          </button>
        </form>
      </li>
    </ul>
  </div>

  <h6 class="fw-bold mb-1"><?= htmlspecialchars($a['title']) ?></h6>
  <?php
    $msg = htmlspecialchars($a['message']);
    $isLong = strlen($msg) > 150;
  ?>
  <div class="announcement-text pe-4">
    <?= $isLong ? substr($msg, 0, 150) . '...' : $msg ?>
    <?php if ($isLong): ?>
      <a href="#" class="text-primary show-more" data-message="<?= $msg ?>">Show more</a>
    <?php endif; ?>
    <small class="text-muted d-block mt-2">
      <?= date("M d, Y | h:ia", strtotime($a['encoded_at'])) ?> by <?= getMemberName($conn, $a['encoded_by']) ?>
    </small>
  </div>
</div>
