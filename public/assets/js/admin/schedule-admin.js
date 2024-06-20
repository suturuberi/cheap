document.addEventListener('DOMContentLoaded', function() {
    const calendar = document.querySelector('.calendar');
    const month_names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    const isLeapYear = (year) => {
        return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
    };

    const getFebDays = (year) => {
        return isLeapYear(year) ? 29 : 28;
    };

    const generateCalendar = (month, year) => {
        let calendar_days = calendar.querySelector('.calendar-days');
        let calendar_header_year = calendar.querySelector('#year');

        let days_of_month = [31, getFebDays(year), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        calendar_days.innerHTML = '';

        let currDate = new Date();
        if (!month) month = currDate.getMonth();
        if (!year) year = currDate.getFullYear();

        let curr_month = `${month_names[month]}`;
        month_picker.innerHTML = curr_month;
        calendar_header_year.innerHTML = year;

        let first_day = new Date(year, month, 1);

        for (let i = 0; i <= days_of_month[month] + first_day.getDay() - 1; i++) {
            let day = document.createElement('div');
            if (i >= first_day.getDay()) {
                day.classList.add('calendar-day');
                let date = `${year}-${(month + 1).toString().padStart(2, '0')}-${(i - first_day.getDay() + 1).toString().padStart(2, '0')}`;
                day.innerHTML = i - first_day.getDay() + 1;
                day.innerHTML += `<span></span><span></span><span></span><span></span>`;
                day.setAttribute('data-date', date);

                if (date === currDate.toISOString().split('T')[0]) {
                    day.classList.add('curr-date');
                }

                let hasRegularSchedule = regularSchedules.some(schedule => {
                    let scheduleStartDate = new Date(schedule.sched_start_date);
                    let scheduleEndDate = new Date(schedule.sched_end_date);
                    let currentDate = new Date(date);
                    return scheduleStartDate <= currentDate && scheduleEndDate >= currentDate;
                });

                let hasSaleSchedule = saleSchedules.some(schedule => {
                    let scheduleStartDate = new Date(schedule.sched_start_date);
                    let scheduleEndDate = new Date(schedule.sched_end_date);
                    let currentDate = new Date(date);
                    return currentDate >= scheduleStartDate && currentDate <= scheduleEndDate;
                });

                if (hasRegularSchedule && hasSaleSchedule) {
                    day.classList.add('both-schedules'); // Add a class for both regular and sale schedules
                } else {
                    if (hasRegularSchedule) {
                        day.classList.add('schedule-type-regular');
                    }
                    if (hasSaleSchedule) {
                        day.classList.add('schedule-type-sale');
                    }
                }

                day.addEventListener('click', function() {
                    showSchedules(this.getAttribute('data-date'));
                });
            } else {
                day.classList.add('empty-cell');
            }
            calendar_days.appendChild(day);
        }

        console.log(regularSchedules);
        console.log(saleSchedules);
        console.log(schedules);
    };

    const showSchedules = (date) => {
        let dateObj = new Date(date);
        let options = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('t-day').innerText = dateObj.toLocaleDateString('en-US', { weekday: 'long' });
        document.getElementById('t-date').innerText = dateObj.toLocaleDateString('en-US', options);

        let scheduleContainer = document.querySelector('.schedules');
        scheduleContainer.innerHTML = '';

        let filteredRegularSchedules = regularSchedules.filter(schedule => {
            let scheduleStartDate = new Date(schedule.sched_start_date);
            let scheduleEndDate = new Date(schedule.sched_end_date);
            let currentDate = new Date(date);

            if (scheduleStartDate > currentDate) {
                console.log(scheduleStartDate);
                console.log('button reg')
            }
            return currentDate >= scheduleStartDate && currentDate <= scheduleEndDate;
        });

        let filteredSaleSchedules = saleSchedules.filter(schedule => {
            let scheduleStartDate = new Date(schedule.sched_start_date);
            let scheduleEndDate = new Date(schedule.sched_end_date);
            let currentDate = new Date(date);
            if (scheduleStartDate > currentDate) {
                console.log(scheduleStartDate);
                console.log('button sale')
            }
            return currentDate >= scheduleStartDate && currentDate <= scheduleEndDate;
        });

        if (filteredRegularSchedules.length === 0 && filteredSaleSchedules.length === 0) {
            scheduleContainer.innerHTML = '<p>No schedules for this date</p>';
        } else {
            if (filteredRegularSchedules.length > 0) {
                filteredRegularSchedules.forEach(schedule => {
                    let scheduleElement = document.createElement('div');
                    scheduleElement.classList.add('schedule-item');
                    
                    scheduleElement.innerHTML = `
                        <figure>
                            <blockquote class="blockquote">
                                <span class="item-description">${schedule.item_name} ${schedule.item_col_descript} ${schedule.item_size_descript}</span>
                                <span class="sched-date"><strong>${schedule.sched_start_date} <span class="date-divider">to</span> ${schedule.sched_end_date}</strong></span>
                            </blockquote>
                            <figcaption class="blockquote-footer">
                                <div class="schedule-info">
                                    <span class="admin">${schedule.admin_fname} ${schedule.admin_lname}</span>
                                </div>
                            </figcaption>
                        </figure>
                    `;

                    scheduleContainer.appendChild(scheduleElement);
                });
            }

            if (filteredSaleSchedules.length > 0) {
                filteredSaleSchedules.forEach(schedule => {
                    let scheduleElement = document.createElement('div');
                    scheduleElement.classList.add('schedule-item');
                    
                    scheduleElement.innerHTML = `
                        <figure>
                            <blockquote class="blockquote">
                                <span class="item-description">${schedule.sale_descript}</span>
                                <span class="sched-date"><strong>${schedule.sched_start_date} <span class="date-divider">to</span> ${schedule.sched_end_date}</strong></span>
                            </blockquote>
                            <figcaption class="blockquote-footer">
                                <div class="schedule-info">
                                    <span class="admin">${schedule.admin_fname} ${schedule.admin_lname}</span>
                                </div>
                            </figcaption>
                        </figure>
                    `;

                    scheduleContainer.appendChild(scheduleElement);
                });
            }
        }
    };

    const scheduleTypeRadioButtons = document.querySelectorAll('input[name="schedule_type"]');
    const itemSelect = document.getElementById('itemv_id');
    const saleSelect = document.getElementById('sale_id');
    const adminSelect = document.getElementById('admin_id');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    let month_list = calendar.querySelector('.month-list');
    month_names.forEach((e, index) => {
        let month = document.createElement('div');
        month.innerHTML = `<div data-month="${index}">${e}</div>`;
        month.querySelector('div').onclick = () => {
            month_list.classList.remove('show');
            curr_month.value = index;
            generateCalendar(index, curr_year.value);
        };
        month_list.appendChild(month);
    });

    let month_picker = calendar.querySelector('#month-picker');
    month_picker.onclick = () => {
        month_list.classList.add('show');
    };

    let currDate = new Date();

    let curr_month = { value: currDate.getMonth() };
    let curr_year = { value: currDate.getFullYear() };

    generateCalendar(curr_month.value, curr_year.value);
    showSchedules(currDate.toISOString().split('T')[0]);

    document.querySelector('#prev-year').onclick = () => {
        --curr_year.value;
        generateCalendar(curr_month.value, curr_year.value);
    };

    document.querySelector('#next-year').onclick = () => {
        ++curr_year.value;
        generateCalendar(curr_month.value, curr_year.value);
    };
});
