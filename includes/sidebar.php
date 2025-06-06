<?php
$current_page = $_SERVER['REQUEST_URI'];
 

function getSystemName() {
    global $conn;
    $result = $conn->query("SELECT system_name FROM settings");
    return ($result->num_rows > 0) ? $result->fetch_assoc()['system_name'] : 'RCLS';
}
 
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $positionName = '';

    $sql = "SELECT cp.position_name 
            FROM club_position cp
            INNER JOIN members m ON cp.id = m.role 
            WHERE m.id = '$userId'";

    $positionResult = $conn->query($sql);

    if ($positionResult && $positionRow = $positionResult->fetch_assoc()) {
        $positionName = $positionRow['position_name'];
    }
}
 


function getLogoUrl() {
    global $conn;
    $result = $conn->query("SELECT logo FROM settings");
    if ($result->num_rows > 0) {
        $logoFile = $result->fetch_assoc()['logo'];
        $path = "/rotary/uploads/" . $logoFile;
        return file_exists($_SERVER['DOCUMENT_ROOT'] . $path) ? $path : "/rotary/dist/img/AdminLTELogo.png";
    }
    return "/rotary/dist/img/AdminLTELogo.png";
}
?>
<?php
// Function to check if the user has a specific club position
function hasClubPosition($conn, $memberId, $allowedPositions = []) {
    $placeholders = implode(',', array_fill(0, count($allowedPositions), '?'));
    $types = str_repeat('s', count($allowedPositions)); // assuming position_name is VARCHAR (string)

    $query = "SELECT cp.position_name 
                    FROM members m
                    JOIN club_position cp ON m.role = cp.id
                    WHERE m.id = ? AND cp.position_name IN ($placeholders)";

    $stmt = $conn->prepare($query);
    
    // Combine member_id + allowedPositions
    $params = array_merge([$memberId], $allowedPositions);
    $stmt->bind_param('i' . $types, ...$params);
    
    $stmt->execute();
    $stmt->store_result();
    $hasPosition = $stmt->num_rows > 0;
    $stmt->close();

    return $hasPosition;
}
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Logo & System Name -->
    <div class="text-center"
         style="padding: 10px 0 0 0; margin: 0 !important; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <a href="#" class="d-block" style="margin: 0; padding: 0;">
            <img src="<?php echo getLogoUrl(); ?>" 
                 alt="Logo" 
                 class="img-circle elevation-3"
                 style="width: 65px; height: 65px; object-fit: cover; margin: 0 auto; display: block;">
            <div class="brand-text font-weight-bold text-white" 
                 style="font-size: 1rem; line-height: 1.1; margin: 2px 0 0 0;">
                <?php echo getSystemName(); ?>
            </div>
        </a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" style="padding-top: 4px !important; margin-top: 0 !important;">
        <!-- User Panel -->
        <div class="user-panel d-flex align-items-center" 
             style="background-color: #f8f9fa; border-radius: 8px; padding: 10px; margin: 0 !important;">
            <div class="image me-3">
                <?php
                $photoPath = !empty($_SESSION['photo']) 
                    ? '/rotary/uploads/member_photos/' . $_SESSION['photo'] 
                    : '/rotary/uploads/member_photos/default.jpg';
                $fullPath = $_SERVER['DOCUMENT_ROOT'] . $photoPath;
                $photoDisplay = file_exists($fullPath) ? $photoPath : '/rotary/uploads/member_photos/default.jpg';
                ?>
                <img src="<?php echo htmlspecialchars($photoDisplay); ?>" 
                     class="img-size-50 img-circle elevation-2" 
                     alt="Member Photo" 
                     style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #007bff;">
            </div>
            <div class="info flex-grow-1">
                <a href="/rotary/webpages/manage-profile/my-profile/my_profile.php" 
                   class="d-block fw-semibold text-dark" 
                   style="font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Guest'); ?>
                </a>
                <?php if (!empty($positionName)): ?>
                    <small class="d-block mt-1 px-2 py-1 rounded" 
                           style="background-color: #007bff; color: white; font-size: 0.85rem; max-width: fit-content; box-shadow: 0 0 6px rgba(0, 123, 255, 0.6);">
                        <?php echo htmlspecialchars($positionName); ?>
                    </small>
                <?php else: ?>
                    <small class="d-block mt-1 text-muted" style="font-size: 0.85rem;">No Role Assigned</small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                <!-- Announcements -->
                <li class="nav-item">
                    <a href="/rotary/announcements/announcements.php" class="nav-link <?php echo (strpos($current_page, 'announcements.php') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-bullhorn me-2"></i><p>Announcements</p>
                    </a>
                </li>

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="/rotary/dashboard/dashboard.php" class="nav-link <?php echo (strpos($current_page, 'dashboard.php') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt me-2"></i><p>Dashboard</p>
                    </a>
                </li>

                <!-- Manage Profile -->
                <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                ?>

                <li class="nav-item has-treeview <?php echo (
                    $current_page === 'my_profile.php' || 
                    $current_page === 'personal_transaction.php'
                ) ? 'menu-open' : ''; ?>">

                    <a href="#" class="nav-link <?php echo (
                        $current_page === 'my_profile.php' || 
                        $current_page === 'personal_transaction.php'
                    ) ? 'active' : ''; ?>">
                        
                        <i class="nav-icon fas fa-user"></i><p>My Profile<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/rotary/webpages/manage-profile/my-profile/my_profile.php" class="nav-link <?php echo ($current_page === 'my_profile.php') ? 'active' : ''; ?>">
                            <p class="text-right w-100">My Profile</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/rotary/webpages/manage-profile/personal-transaction/personal_transaction.php" class="nav-link <?php echo ($current_page === 'personal_transaction.php') ? 'active' : ''; ?>">
                            <p class="text-right w-100">Personal Transactions</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Club Members -->
                <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3'|| $_SESSION['role'] === '5'|| $_SESSION['role'] === '100'): ?>
                <li class="nav-item has-treeview <?php echo (
                    strpos($current_page, 'add_member.php') !== false ||
                    strpos($current_page, 'list_renewal.php') !== false ||
                    strpos($current_page, 'renew.php') !== false ||
                    strpos($current_page, 'manage_members.php') !== false ||
                    strpos($current_page, 'edit_member.php') !== false ||
                    strpos($current_page, 'member_profile.php') !== false
                ) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (
                        strpos($current_page, 'add_member.php') !== false ||
                        strpos($current_page, 'list_renewal.php') !== false ||
                        strpos($current_page, 'renew.php') !== false ||
                        strpos($current_page, 'manage_members.php') !== false ||
                        strpos($current_page, 'edit_member.php') !== false ||
                        strpos($current_page, 'member_profile.php') !== false
                    ) ? 'active' : ''; ?>">
                       
                        <i class="nav-icon fas fa-users"></i><p>Club Members<i class="fas fa-angle-left right"></i></p>
                    </a>
                   
                    <ul class="nav nav-treeview">
                    <?php if (  $_SESSION['role'] === '3' || $_SESSION['role'] === '100'): ?> 
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-members/add-member/add_member.php" class="nav-link <?php echo (strpos($current_page, 'add_member.php') !== false) ? 'active' : ''; ?>">
                            <p class="text-right w-100">Add Member</p>
                            </a>
                        </li>

                        <?php if (  $_SESSION['role'] === '3'|| $_SESSION['role'] === '100'): ?>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-members/manage-roles/manage_roles.php" class="nav-link <?php echo (
                                strpos($current_page, 'manage_roles.php') !== false
                            ) ? 'active' : ''; ?>">
                               <p class="text-right w-100">Manage Roles</p>
                            </a>
                        </li>

                        <?php endif; ?>
                        <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3'|| $_SESSION['role'] === '5'|| $_SESSION['role'] === '100'): ?>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-members/manage-members/manage_members.php" class="nav-link <?php echo (
                                strpos($current_page, 'manage_members.php') !== false ||
                                strpos($current_page, 'edit_member.php') !== false ||
                                strpos($current_page, 'member_profile.php') !== false
                            ) ? 'active' : ''; ?>">
                               <p class="text-right w-100">Manage Members</p>
                            </a>
                        </li>
                       
                        <?php if (  $_SESSION['role'] === '3'|| $_SESSION['role'] === '100'): ?>  
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-members/renewal/list_renewal.php" class="nav-link <?php echo (
                                strpos($current_page, 'list_renewal.php') !== false ||
                                strpos($current_page, 'renew.php') !== false
                            ) ? 'active' : ''; ?>">
                               <p class="text-right w-100">Member Renewal</p>
                            </a>
                        </li>

                        <?php endif; ?>
                        
                        <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Club Attendances -->
                <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3'|| $_SESSION['role'] === '100' ): ?>
                <li class="nav-item has-treeview <?php echo (
                                strpos($current_page, 'add_attendance.php') !== false ||
                                strpos($current_page, 'manage_attendances.php') !== false ||
                                strpos($current_page, 'delete_attendance.php') !== false ||
                                strpos($current_page, 'edit_attendance.php') !== false
                ) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (
                                strpos($current_page, 'add_attendance.php') !== false ||
                                strpos($current_page, 'manage_attendances.php') !== false ||
                                strpos($current_page, 'delete_attendance.php') !== false ||
                                strpos($current_page, 'edit_attendance.php') !== false
                    ) ? 'active' : ''; ?>">
                    
                        <i class="nav-icon fas fa-calendar-check"></i><p>Club Attendances<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                    

                    <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3'|| $_SESSION['role'] === '100' ): ?>  
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-attendances/add-attendance/add_attendance.php" class="nav-link <?php echo (strpos($current_page, 'add_attendance.php') !== false) ? 'active' : ''; ?>">
                            <p class="text-right w-100">Add Attendance</p>
                            </a>
                        </li>
                    
                        <?php endif; ?>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-attendances/manage-attendances/manage_attendances.php" class="nav-link <?php echo (
                                strpos($current_page, 'manage_attendances.php') !== false ||
                                strpos($current_page, 'delete_attendance.php') !== false ||
                                strpos($current_page, 'edit_attendance.php') !== false

                            ) ? 'active' : ''; ?>">
                                 <p class="text-right w-100">Manage Attendances</p>
                            </a>
                        </li>
                 
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Club wallets -->
                <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3'|| $_SESSION['role'] === '100' ): ?>
                <li class="nav-item has-treeview <?php echo (
                                strpos($current_page, 'add_wallet.php') !== false ||
                                strpos($current_page, 'manage_wallets.php') !== false ||
                                strpos($current_page, 'delete_wallet.php') !== false ||
                                strpos($current_page, 'edit_wallet.php') !== false
                ) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (
                                strpos($current_page, 'add_wallet.php') !== false ||
                                strpos($current_page, 'manage_wallets.php') !== false ||
                                strpos($current_page, 'delete_wallet.php') !== false ||
                                strpos($current_page, 'edit_wallet.php') !== false
                    ) ? 'active' : ''; ?>">
                    
                        <i class="nav-icon fas fa-wallet"></i><p>Club Wallets<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                    

                    <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3'|| $_SESSION['role'] === '100' ): ?>  
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-wallets/add-wallet/add_wallet.php" class="nav-link <?php echo (strpos($current_page, 'add_wallet.php') !== false) ? 'active' : ''; ?>">
                            <p class="text-right w-100">Add Wallet</p>
                            </a>
                        </li>
                    
                        <?php endif; ?>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-wallets/manage-wallets/manage_wallets.php" class="nav-link <?php echo (
                                strpos($current_page, 'manage_wallets.php') !== false ||
                                strpos($current_page, 'delete_wallet.php') !== false ||
                                strpos($current_page, 'edit_wallet.php') !== false

                            ) ? 'active' : ''; ?>">
                                 <p class="text-right w-100">Manage Wallets</p>
                            </a>
                        </li>
                 
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Club Transaction -->
                <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '4' || $_SESSION['role'] === '5' || $_SESSION['role'] === '100'): ?> 
                <li class="nav-item has-treeview <?php echo (
                    strpos($current_page, 'add_transaction.php') !== false ||
                    strpos($current_page, 'manage_transactions.php') !== false ||
                    strpos($current_page, 'edit_transactions.php') !== false ||
                    strpos($current_page, 'view_receipt.php') !== false

                ) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (
                        strpos($current_page, 'add_transaction.php') !== false ||
                        strpos($current_page, 'manage_transactions.php') !== false ||
                        strpos($current_page, 'edit_transactions.php') !== false ||
                        strpos($current_page, 'view_receipt.php') !== false

                    ) ? 'active' : ''; ?>">
                    
                        <i class="nav-icon fas fa-exchange-alt"></i><p>Club Transactions<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                    

                    <?php if ( $_SESSION['role'] === '4'||$_SESSION['role'] === '100'  ): ?>  
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-transactions/add-transaction/add_transaction.php" class="nav-link <?php echo (strpos($current_page, 'add_transaction.php') !== false) ? 'active' : ''; ?>">
                            <p class="text-right w-100">Add Transaction</p>
                            </a>
                        </li>
                      
                        <?php endif; ?>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-transactions/manage-transactions/manage_transactions.php" class="nav-link <?php echo (
                                strpos($current_page, 'manage_transactions.php') !== false ||
                                strpos($current_page, 'edit_transactions.php') !== false ||
                                strpos($current_page, 'view_receipt.php') !== false

                            ) ? 'active' : ''; ?>">
                                 <p class="text-right w-100">Manage Transactions</p>
                            </a>
                        </li>
                 
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Club Projects -->
                <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3' || $_SESSION['role'] === '5'|| $_SESSION['role'] === '6'|| $_SESSION['role'] === '100'): ?>  
                <li class="nav-item has-treeview <?php echo (
                    strpos($current_page, 'add_project.php') !== false ||
                    strpos($current_page, 'manage_projects.php') !== false ||
                    strpos($current_page, 'project_details.php') !== false ||
                    strpos($current_page, 'edit_project.php') !== false 
                ) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (
                        strpos($current_page, 'add_project.php') !== false ||
                        strpos($current_page, 'manage_projects.php') !== false ||
                        strpos($current_page, 'project_details.php') !== false ||
                        strpos($current_page, 'edit_project.php') !== false
                    ) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tasks"></i><p>Club Projects<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                     
                    <?php if ( $_SESSION['role'] === '3' ||$_SESSION['role'] === '100'): ?>  
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-projects/add-project/add_project.php" 
                            class="nav-link <?php echo (strpos($current_page, 'add_project.php') !== false) ? 'active' : ''; ?>">
                            <p class="text-right w-100">Add Project</p>
                            </a>
                        </li>
                    <?php endif; ?>
                        
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-projects/manage-projects/manage_projects.php" 
                            class="nav-link <?php echo (
                                strpos($current_page, 'manage_projects.php') !== false ||
                                strpos($current_page, 'project_details.php') !== false ||
                                strpos($current_page, 'edit_project.php') !== false
                            ) ? 'active' : ''; ?>">
                                <p class="text-right w-100">
                                    <?php echo (intval($_SESSION['role']) === 6 || $_SESSION['role']=== 100) ? 'View Projects' : 'Manage Projects'; ?>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Club Events -->
                <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3'|| $_SESSION['role'] === '5'|| $_SESSION['role'] === '6'|| $_SESSION['role'] === '100'): ?>
                <li class="nav-item has-treeview <?php echo (
                    strpos($current_page, 'add_event.php') !== false ||
                    strpos($current_page, 'manage_events.php') !== false ||
                    strpos($current_page, 'edit_event.php') !== false ||
                    strpos($current_page, 'event_details.php') !== false
                ) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (
                        strpos($current_page, 'add_event.php') !== false ||
                        strpos($current_page, 'manage_events.php') !== false ||
                        strpos($current_page, 'edit_event.php') !== false ||
                        strpos($current_page, 'event_details.php') !== false
                    ) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-calendar-alt"></i><p>Club Events<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <?php if ($_SESSION['role'] == '3'|| $_SESSION['role'] === '100'): ?>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-events/add-event/add_event.php" class="nav-link <?php echo (strpos($current_page, 'add_event.php') !== false) ? 'active' : ''; ?>">
                            <p class="text-right w-100">Add Event</p>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3'|| $_SESSION['role'] === '5' || $_SESSION['role'] === '6'|| $_SESSION['role'] === '100'): ?>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-events/manage-events/manage_events.php" class="nav-link <?php echo (
                                strpos($current_page, 'manage_events.php') !== false ||
                                strpos($current_page, 'edit_event.php') !== false ||
                                strpos($current_page, 'delete_event.php') !== false ||
                                strpos($current_page, 'event_details.php') !== false
                            ) ? 'active' : ''; ?>">
                             <p class="text-right w-100">
                             <?php echo (intval($_SESSION['role']) === 6 || $_SESSION['role']=== 100) ? 'View Events' : 'Manage Events'; ?>
</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Club Operations -->
                <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3'|| $_SESSION['role'] === '4' || $_SESSION['role'] === '5'|| $_SESSION['role'] === '6'|| $_SESSION['role'] === '100'): ?>
                <li class="nav-item has-treeview <?php echo (
                    strpos($current_page, 'add_operation.php') !== false ||
                    strpos($current_page, 'delete_operation.php') !== false ||
                    strpos($current_page, 'edit_operation.php') !== false ||
                    strpos($current_page, 'manage_operations.php') !== false ||
                    strpos($current_page, 'operation_details.php') !== false 
                    
                ) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (
                        strpos($current_page, 'add_operation.php') !== false ||
                        strpos($current_page, 'delete_operation.php') !== false ||
                        strpos($current_page, 'edit_operation.php') !== false ||
                        strpos($current_page, 'manage_operations.php') !== false ||
                        strpos($current_page, 'operation_details.php') !== false

                    ) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users-cog"></i><p>Club Operations<i class="fas fa-angle-left right"></i></p>
                    </a>
                    
                    <ul class="nav nav-treeview">
                  
                    <?php if ( $_SESSION['role'] === '3'|| $_SESSION['role'] === '4'|| $_SESSION['role'] === '100'): ?>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-operations/add-operation/add_operation.php" class="nav-link <?php echo (strpos($current_page, 'add_operation.php') !== false) ? 'active' : ''; ?>">
                            <p class="text-right w-100">Add Operation</p>
                            </a>
                        </li>
                        <?php endif; ?> 
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-operations/manage-operations/manage_operations.php" class="nav-link <?php echo (
                                strpos($current_page, 'manage_operations.php') !== false ||
                                strpos($current_page, 'delete_operation.php') !== false ||
                                strpos($current_page, 'operation_details.php') !== false ||
                                strpos($current_page, 'edit_operation.php') !== false
                            ) ? 'active' : ''; ?>">
                               <p class="text-right w-100">
                                <?php echo (intval($_SESSION['role']) === 6 || $_SESSION['role']=== 100)? 'View Operations' : 'Manage Operations'; ?>
                                </p>

                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?> 

                <!-- Club Reports -->
                <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '3' || $_SESSION['role'] === '4' || $_SESSION['role'] === '6' || $_SESSION['role'] === '100'): ?>
                <li class="nav-item has-treeview <?php echo (
                    strpos($current_page, 'member_report.php') !== false ||
                    strpos($current_page, 'wallet_report.php') !== false ||
                    strpos($current_page, 'wallet_details.php') !== false ||
                    strpos($current_page, 'club_report.php') !== false
                ) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (
                        strpos($current_page, 'member_report.php') !== false ||
                        strpos($current_page, 'wallet_report.php') !== false ||
                        strpos($current_page, 'wallet_details.php') !== false ||
                        strpos($current_page, 'club_report.php') !== false
                    ) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-coins"></i>
                        <p>Club Reports<i class="fas fa-angle-left right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-reports/member-report/member_report.php" class="nav-link <?php echo (strpos($current_page, 'member_report.php') !== false) ? 'active' : ''; ?>">
                                <p class="text-right w-100">
                                    <?php echo (intval($_SESSION['role']) === 6 || $_SESSION['role'] === 100) ? 'View Member Report' : 'Member Report'; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-reports/wallet-report/wallet_report.php" class="nav-link <?php echo (
                                strpos($current_page, 'wallet_report.php') !== false || strpos($current_page, 'wallet_details.php') !== false
                            ) ? 'active' : ''; ?>">
                                <p class="text-right w-100">
                                    <?php echo (intval($_SESSION['role']) === 6 || $_SESSION['role'] === 100) ? 'View Wallet Report' : 'Wallet Report'; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="/rotary/webpages/club-reports/club-report/club_report.php" class="nav-link <?php echo (strpos($current_page, 'club_report.php') !== false) ? 'active' : ''; ?>">
                                <p class="text-right w-100">
                                    <?php echo (intval($_SESSION['role']) === 6 || $_SESSION['role'] === 100) ? 'View Club Report' : 'Club Report'; ?>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php endif; ?>
                <!-- Audit Logs -->
                <?php if ($_SESSION['role'] === '1' || $_SESSION['role'] === '100'): ?>
                <li class="nav-item">
                    <a href="/rotary/webpages/audit-logs/audit_logs.php" class="nav-link <?php echo (strpos($current_page, 'audit_logs.php') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i><p>Audit Logs</p>
                    </a>
                </li>

                <!-- Settings -->
                <li class="nav-item">
                    <a href="/rotary/webpages/settings/settings.php" class="nav-link <?php echo (strpos($current_page, 'settings.php') !== false) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-cogs"></i><p>Settings</p>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Logout -->
                <li class="nav-item">
                    <a href="/rotary/webpages/logout/logout.php" class="nav-link">
                        <i class="nav-icon fas fa-power-off"></i><p>Logout</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>