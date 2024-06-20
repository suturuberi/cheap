<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="../public/assets/css/superadmin/schedule-superadmin.css">
    <title>Schedule Superadmin</title>
  </head>
  <body>
    <div class="content px-5">
      <div class="light mb-3">
        <div class="mb-2">
          <div class="row">
              <div class="col-md-8">
                  <h3 class="fw-bold fs-4 my-3">Schedule</h3>
              </div>
          </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-dark text-center" role="alert">
                <?= implode('<br>', $errors) ?>
            </div>
        <?php endif; ?>
  
        <div class="row border rounded-5 p-3 bg-white shadow box-area">
          <div class="col-md-6 left-box">
            <div class="calendar">
              <div class="calendar-header bg-dark rounded">
                  <span class="month-picker" id="month-picker">February</span>
                  <div class="year-picker">
                      <span class="year-change" id="prev-year">
                        <i class="fas fa-angle-left prev"></i>
                      </span>
                      <span id="year">2021</span>
                      <span class="year-change" id="next-year">
                        <i class="fas fa-angle-right next"></i>
                      </span>
                  </div>
              </div>
              <div class="calendar-body">
                  <div class="calendar-week-day">
                      <div>Sun</div>
                      <div>Mon</div>
                      <div>Tue</div>
                      <div>Wed</div>
                      <div>Thu</div>
                      <div>Fri</div>
                      <div>Sat</div>
                  </div>
                  <div class="calendar-days"></div>
              </div>
              <div class="calendar-footer">
              </div>
              <div class="month-list"></div>
            </div>
          </div>
  
          <div class="col-md-6 right-box">
            <div class="row align-items-center">
              <div class="mb-4 mt-5">
                <div class="t-date">
                  <strong class="t-day" id="t-day"></strong>
                  <strong class="t-date text-muted" id="t-date"></strong>
                </div>
              </div>
              
              <div class="mb-3">
                <div class="schedules border rounded p-3 bg-white shadow box-area">
                </div>
              </div>

              <div id="modal-container"></div>
              
            </div>
          </div>
          <div class="col-md-12 fs-4 my-3 d-flex justify-content-md-end">
            <button type="button" class="btn btn-dark button-add" title="Add Schedule" data-bs-toggle="modal" data-bs-target="#modal-add"><i class="fas fa-plus"></i></button>
  
            <div class="modal fade" id="modal-add" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="modal-title">Add Schedule</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                        <div class="modal-body">
                          <div class="mb-3">
                            <label for="schedule_type" class="form-label fs-6">Schedule Type</label>
                            <div>
                                <?php if (isset($data['schedt']) && !empty($data['schedt'])): ?>
                                    <?php foreach ($data['schedt'] as $scheduleType): ?>
                                        <div>
                                            <input type="radio" id="schedule_type_<?= $scheduleType['schedt_id']; ?>" name="schedule_type" value="<?= $scheduleType['schedt_id']; ?>" required>
                                            <label class="fs-6" for="schedule_type_<?= $scheduleType['schedt_id']; ?>"><?= $scheduleType['schedt_descript']; ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No schedule types available.</p>
                                <?php endif; ?>
                            </div>
                          </div>
                            <div class="mb-3">
                                <select class="form-select btn-secondary" id="itemv_id" name="itemv_id" aria-label="Item" disabled required>
                                  <?php if (empty($item_vars)): ?>
                                      <option value="" disabled>No item variation available</option>
                                        <?php else: ?>
                                      <option selected disabled>Select Item</option>
                                        <?php foreach ($item_vars as $variation): ?>
                                          <option value="<?= $variation['itemv_id'] ?>">
                                              <?= $variation['item_name'] . " " . $variation['item_col_descript'] . " " . $variation['item_size_descript']?>
                                          </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <select class="form-select btn-secondary" id="sale_id" name="sale_id" aria-label="Sale" disabled required>
                                  <?php if (empty($sales)): ?>
                                      <option value="" selected disabled>No sale available</option>
                                        <?php else: ?>
                                      <option selected disabled>Select Sale</option>
                                        <?php foreach ($sales as $sale): ?>
                                            <option value="<?= $sale['sale_id'] ?>"><?= $sale['sale_descript'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                                <div class="mb-3">
                                  <select class="form-select btn-secondary" id="admin_id" name="admin_id" aria-label="Admin" disabled required>
                                    <?php if (empty($admins)) : ?>
                                            <option value="" disabled selected>No admins available</option>
                                        <?php else : ?>
                                            <option value="" selected>Select admin</option>
                                            <?php foreach ($admins as $admin) : ?>
                                                <option value="<?= $admin['admin_id'] ?>"><?= $admin['admin_fname'] ?> <?= $admin['admin_lname'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                </select>
                                </div>
                                <div class="mb-3">
                                  <input type="date" id="start_date" name="start_date" class="form-control form-control-lg fs-6" placeholder="Starting Date" onchange="this.className=(this.value!=''?'has-value':'')" disabled required>
                                </div>
                              <div class="mb-3">
                                  <input type="date" id="end_date" name="end_date" class="form-control form-control-lg fs-6" placeholder="End Date" onchange="this.className=(this.value!=''?'has-value':'')" disabled required>
                              </div>
                        </div>
  
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="add_sched" name="add_sched" class="btn btn-dark">Add Schedule</button>
                        </div>
                      </form>
                    </div>
                </div>
            </div>  
          </div>

          <div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="modal-edit-title" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content">
                      <div class="modal-header">
                          <h1 class="modal-title fs-5" id="modal-edit-title"></h1>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <form method="post" id="edit-schedule-form">
                          <div class="modal-body">
                              <div class="mb-3">
                                  <select class="form-select btn-secondary" id="ed-itemv-id" name="ed-itemv-id" aria-label="Item" required disabled>
                                    <?php if (empty($item_vars)): ?>
                                      <option value="" disabled>No item variation available</option>
                                        <?php else: ?>
                                      <option selected disabled>Select Item</option>
                                        <?php foreach ($item_vars as $variation): ?>
                                          <option value="<?= $variation['itemv_id'] ?>">
                                              <?= $variation['item_name'] . " " . $variation['item_col_descript'] . " " . $variation['item_size_descript']?>
                                          </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                  </select>
                              </div>
                              <div class="mb-3">
                                  <select class="form-select btn-secondary" id="ed-sale-id" name="ed-sale-id" aria-label="Sale" required disabled>
                                    <?php if (empty($sales_s)): ?>
                                        <option value="" selected disabled>No sale available</option>
                                          <?php else: ?>
                                        <option selected disabled>Select Sale</option>
                                          <?php foreach ($sales_s as $sale): ?>
                                              <option value="<?= $sale['sale_id'] ?>"><?= $sale['sale_descript'] ?></option>
                                          <?php endforeach; ?>
                                      <?php endif; ?>
                                  </select>
                              </div>
                              <div class="mb-3">
                                  <select class="form-select btn-secondary" id="ed-admin-id" name="ed-admin-id" aria-label="Admin" required disabled>
                                  <?php if (empty($admins)) : ?>
                                            <option value="" disabled selected>No admins available</option>
                                        <?php else : ?>
                                            <option value="" selected>Select admin</option>
                                            <?php foreach ($admins as $admin) : ?>
                                                <option value="<?= $admin['admin_id'] ?>"><?= $admin['admin_fname'] ?> <?= $admin['admin_lname'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                  </select>
                              </div>
                              <div class="mb-3">
                                  <input type="date" id="edit-start-date" name="edit-start-date" class="form-control form-control-lg fs-6" value="" placeholder="Starting Date" required>
                              </div>
                              <div class="mb-3">
                                  <input type="date" id="edit-end-date" name="edit-end-date" class="form-control form-control-lg fs-6" value="" placeholder="End Date" required>
                              </div>
                          </div>
                          <div class="modal-footer">
                              <input type="hidden" id="edit-itemv-id" name="edit-itemv-id" value="">
                              <input type="hidden" id="edit-sale-id" name="edit-sale-id" value="">
                              <input type="hidden" id="edit-admin-id" name="edit-admin-id" value="">
                              <input type="hidden" name="edit-sched-id" id="edit-sched-id" value="">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" id="edit_sched" name="edit_sched" class="btn btn-dark">Edit Schedule</button>
                          </div>
                      </form>
                  </div>
              </div>
          </div>

          
          <div class="modal fade" id="modal-delete" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="modal-title">Delete Schedule</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <p>Are you sure you want to delete this schedule?</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="post">
                      <input type="hidden" name="del-sched-id" id="del-sched-id" value="">
                      <button type="submit" name="del_sched" class="btn btn-dark">Delete</button>
                    </form>
                </div>
              </div>
            </div>  
          </div>
        </div>
      </div>
    </div>
        
    <script>
      let schedules = <?php echo json_encode($data['schedules']); ?>;
      let schedt = <?php echo json_encode($data['schedt']); ?>;
      let regularSchedules = <?php echo json_encode($data['regular_schedules']); ?>;
      let saleSchedules = <?php echo json_encode($data['sale_schedules']); ?>;
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../public/assets/js/superadmin/schedule-superadmin.js"></script>
  </body>
</html>