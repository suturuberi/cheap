<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../public/assets/css/admin/topbar-admin.css"/>
    <title>Topbar</title>
</head>
<body>
    <header class="header">
        <div class="container-fluid">
            <div class="row text-body-secondary">
                <div class="col-6 text-start">
                    <button class="btn bg-transparent text-white" type="button" data-bs-toggle="modal" data-bs-target="#userModal">
                        <i class="lni lni-cog"></i>
                    </button>             
    
                    <strong id="user">
                        <?php echo $data['user']['user_fname'] . ' ' . $data['user']['user_mname'] . ' ' . $data['user']['user_lname']; ?>
                    </strong>
                </div>        

                <div class="col-6 text-end text-body-secondary d-none d-md-block">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <label class="text-body-secondary"><strong id="day"></strong></label>
                        </li>
                        <li class="list-inline-item">
                            <label class="text-body-secondary"><strong id="date"></strong></label>
                        </li>
                        <li class="list-inline-item">
                            <label class="text-body-secondary"><strong id="time"></strong></label>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">User Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <li class="list-group-item">
                            <button class="btn btn-no-decoration" onclick="showModal('#view-profile')">View Profile</button>
                        </li>
                        <li class="list-group-item">
                            <button class="btn btn-no-decoration" onclick="showModal('#change-password')">Change Password</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div> 

    <div class="modal fade" id="view-profile" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="card border-0 shadow custom-card">
                                <div class="card-body text-center">
                                    <h2 class="mb-2"><?php echo $data['user']['user_fname'] . ' ' . $data['user']['user_mname'] . ' ' . $data['user']['user_lname']; ?></h2>
                                    <p class="text-muted mb-1"><?php echo $data['user']['user_email']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 

    <div class="modal fade" id="change-password" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Change Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <input type="password" id="c_pass" name="c_pass" class="form-control form-control-lg bg-light fs-6" placeholder="Current Password">
                        </div>
                        <div class="mb-3">
                            <input type="password" id="n_pass" name="n_pass" class="form-control form-control-lg bg-light fs-6" placeholder="New Password">
                        </div>
                        <div class="mb-3">
                            <input type="password" id="cpass" name="cpass" class="form-control form-control-lg bg-light fs-6" placeholder="Confirm Password">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="changepass" name="changepass" class="btn btn-dark">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../public/assets/js/admin/topbar-admin.js"></script>
</body>
</html>