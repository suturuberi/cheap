<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="../public/assets/css/admin/schedule-admin.css">
    <title>Schedule Admin</title>
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
        </div>
      </div>
    </div>

    <script>
      let schedules = <?php echo json_encode($data['schedules']); ?>;
      let regularSchedules = <?php echo json_encode($data['regularSchedules']); ?>;
      let saleSchedules = <?php echo json_encode($data['saleSchedules']); ?>;
    </script>
    <script src="../public/assets/js/admin/schedule-admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>