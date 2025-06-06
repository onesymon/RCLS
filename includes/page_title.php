<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <?php
                
                if (strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false) 
                {
                    $pageTitle = 'Dashboard';
                } 
                     // ANNOUNCEMENTS
                    elseif (strpos($_SERVER['REQUEST_URI'], 'announcements.php') !== false) 
                    {
                      $pageTitle = 'Overview Club Announcements';
                    }
                    // CLUB MEMBERS
                    elseif (strpos($_SERVER['REQUEST_URI'], 'add_member.php') !== false) 
                    {
                      $pageTitle = 'Add Club Member';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'delete_member.php') !== false) 
                    {
                      $pageTitle = 'Delete Club Member';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'edit_member.php') !== false) 
                    {
                      $pageTitle = 'Edit Club Member';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'manage_members.php') !== false) 
                    {
                      $pageTitle = 'Manage Club Members';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'member_profile.php') !== false) 
                    {
                      $pageTitle = 'Member Profile';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'print_membership_card.php') !== false) 
                    {
                      $pageTitle = 'Print Membership Card';
                    }

                    // RENEWAL
                    elseif (strpos($_SERVER['REQUEST_URI'], 'list_renewal.php') !== false) 
                    {
                      $pageTitle = 'List Renewal';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'renew.php') !== false) 
                    {
                      $pageTitle = 'Renew';
                    }

                    // MANAGE ROLES
                    elseif (strpos($_SERVER['REQUEST_URI'], 'manage_roles.php') !== false) 
                    {
                      $pageTitle = 'Manage Club Roles';
                    }

                    // CLUB ATTENDANCES
                    elseif (strpos($_SERVER['REQUEST_URI'], 'add_attendance.php') !== false) 
                    {
                      $pageTitle = 'Add Club Attendance';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'delete_attendance.php') !== false) 
                    {
                      $pageTitle = 'Delete Member Attendance';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'edit_attendance.php') !== false) 
                    {
                      $pageTitle = 'Edit Member Attendance';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'manage_attendances.php') !== false) 
                    {
                      $pageTitle = 'Manage Club Attendances';
                    }

                    // CLUB WALLETS
                    elseif (strpos($_SERVER['REQUEST_URI'], 'add_wallet.php') !== false) 
                    {
                      $pageTitle = 'Add Club Wallet';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'delete_wallet.php') !== false) 
                    {
                      $pageTitle = 'Delete Club Wallet';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'edit_wallet.php') !== false) 
                    {
                      $pageTitle = 'Edit Club Wallet';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'manage_wallets.php') !== false) 
                    {
                      $pageTitle = 'Manage Club Wallets';
                    }

                    // CLUB TRANSACTIONS
                    elseif (strpos($_SERVER['REQUEST_URI'], 'add_transaction.php') !== false) 
                    {
                      $pageTitle = 'Add Club Transaction';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'delete_transaction.php') !== false) 
                    {
                      $pageTitle = 'Delete Club Transaction';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'edit_transaction.php') !== false) 
                    {
                      $pageTitle = 'Edit Club Transaction';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'manage_transactions.php') !== false) 
                    {
                      $pageTitle = 'Manage Club Transactions';
                    }

                    // CLUB PROJECTS
                    elseif (strpos($_SERVER['REQUEST_URI'], 'add_project.php') !== false) 
                    {
                      $pageTitle = 'Add Club Project';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'delete_project.php') !== false) 
                    {
                      $pageTitle = 'Delete Club Project';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'edit_project.php') !== false) 
                    {
                      $pageTitle = 'Edit Club Project';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'manage_projects.php') !== false) 
                    {
                      $pageTitle = 'Manage Club Projects';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'project_details.php') !== false) 
                    {
                      $pageTitle = 'Project Club Details';
                    }

                    // CLUB EVENTS
                    elseif (strpos($_SERVER['REQUEST_URI'], 'add_event.php') !== false) 
                    {
                      $pageTitle = 'Add Club Event';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'delete_event.php') !== false) 
                    {
                      $pageTitle = 'Delete Club Event';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'edit_event.php') !== false) 
                    {
                      $pageTitle = 'Edit Club Event';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'event_details.php') !== false) 
                    {
                      $pageTitle = 'Event Club Details';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'manage_events.php') !== false) 
                    {
                      $pageTitle = 'Manage Club Events';
                    }

                    // CLUB OPERATIONS
                    elseif (strpos($_SERVER['REQUEST_URI'], 'add_operation.php') !== false) 
                    {
                      $pageTitle = 'Add Club Operation';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'delete_operation.php') !== false) 
                    {
                      $pageTitle = 'Delete Club Operation';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'edit_operation.php') !== false) 
                    {
                      $pageTitle = 'Edit Club Operation';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'manage_operations.php') !== false) 
                    {
                      $pageTitle = 'Manage Club Operations';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'operation_details.php') !== false) 
                    {
                      $pageTitle = 'Operation Club Details';
                    }
                    
                    // LOGIN/LOGOUT
                    elseif (strpos($_SERVER['REQUEST_URI'], 'login.php') !== false) 
                    {
                      $pageTitle = 'Login';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'logout.php') !== false) 
                    {
                      $pageTitle = 'Logout';
                    }


                    // PROFILE
                    elseif (strpos($_SERVER['REQUEST_URI'], 'personal_transaction.php') !== false) 
                    {
                      $pageTitle = 'My Club Transaction';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'profile.php') !== false) 
                    {
                      $pageTitle = 'My Club Profile';
                    }


                    //CLUB REPORTS
                    elseif (strpos($_SERVER['REQUEST_URI'], 'club_report.php') !== false) 
                    {
                      $pageTitle = 'Club Report';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'member_report.php') !== false) 
                    {
                      $pageTitle = 'Club Member Report';
                    }
                     elseif (strpos($_SERVER['REQUEST_URI'], 'wallet_report.php') !== false) 
                    {
                      $pageTitle = 'Club Wallet Report';
                    }
                    elseif (strpos($_SERVER['REQUEST_URI'], 'wallet_details.php') !== false) 
                    {
                      $pageTitle = 'Club Wallet Details';
                    }


                    // SETTINGS
                    elseif (strpos($_SERVER['REQUEST_URI'], 'settings.php') !== false) 
                    {
                      $pageTitle = 'Settings';
                    }

                    // FORMAT
                    elseif (strpos($_SERVER['REQUEST_URI'], '.php') !== false) 
                    {
                      $pageTitle = '';
                    }
                
                echo '<h1 class="m-0 text-dark">' . $pageTitle . '</h1>';
                ?>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/rotary/dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active"><?php echo $pageTitle; ?></li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->