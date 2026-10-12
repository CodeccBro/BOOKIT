<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BookIt - Appointment Booking System</title>



    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">

</head>

<body>



<header>

    <h1>📅 BookIt</h1>

    <p>Smart Appointment Booking System for Salons, Clinics & Small Businesses</p>

</header>



<div class="tabs">

    <button class="tab-btn active" onclick="switchTab('book', this)">

        📝 Book Appointment

    </button>

    <button class="tab-btn" onclick="switchTab('schedule', this)">

        📋 View Schedule

    </button>

</div>



<div class="container">



    <!-- BOOK APPOINTMENT -->

    <div id="book" class="tab-content active">



        <div class="stats-row">

            <div class="stat-card">

                <div class="number" id="stat-total">0</div>

                <div class="label">Total Bookings</div>

            </div>



            <div class="stat-card">

                <div class="number" id="stat-today">0</div>

                <div class="label">Today</div>

            </div>



            <div class="stat-card">

                <div class="number" id="stat-upcoming">0</div>

                <div class="label">Upcoming</div>

            </div>

        </div>



        <div class="card">

            <div class="card-title">

                <div class="icon">📆</div>

                Select Date

            </div>



            <div class="calendar-header">

                <h3 id="currentMonth"></h3>



                <div class="nav-arrows">

                    <button type="button" onclick="changeMonth(-1)">◀</button>

                    <button type="button" onclick="changeMonth(1)">▶</button>

                </div>

            </div>



            <div class="calendar-grid" id="calendar"></div>

        </div>



        <div class="card" id="timeCard" style="display:none;">

            <div class="card-title">

                <div class="icon">⏰</div>

                Select Time Slot —

                <span id="selectedDateDisplay"></span>

            </div>



            <div class="time-slots" id="timeSlots"></div>

        </div>



        <div class="card" id="formCard" style="display:none;">

            <div class="card-title">

                <div class="icon">👤</div>

                Your Details

            </div>



            <div class="booking-summary">

                <h4>📌 Booking Summary</h4>



                <div class="summary-item">

                    <span>Date:</span>

                    <span id="sumDate">-</span>

                </div>



                <div class="summary-item">

                    <span>Time:</span>

                    <span id="sumTime">-</span>

                </div>



                <div class="summary-item">

                    <span>Duration:</span>

                    <span>1 Hour</span>

                </div>

            </div>



            <form id="bookingForm" onsubmit="submitBooking(event)">

                <div class="form-row">

                    <div class="form-group">

                        <label for="customerName">Full Name *</label>

                        <input type="text" id="customerName"

                               placeholder="John Doe" required>

                    </div>



                    <div class="form-group">

                        <label for="customerPhone">Phone Number *</label>

                        <input type="tel" id="customerPhone"

                               placeholder="08012345678" required>

                    </div>

                </div>



                <div class="form-row">

                    <div class="form-group">

                        <label for="customerEmail">Email</label>

                        <input type="email" id="customerEmail"

                               placeholder="john@example.com">

                    </div>



                    <div class="form-group">

                        <label for="serviceType">Service Type *</label>

                        <select id="serviceType" required>

                            <option value="">Select a service...</option>

                            <option value="Consultation">Consultation</option>

                            <option value="Haircut">Haircut</option>

                            <option value="Massage">Massage</option>

                            <option value="Dental Checkup">Dental Checkup</option>

                            <option value="Facial">Facial</option>

                            <option value="Manicure">Manicure</option>

                            <option value="Physical Therapy">Physical Therapy</option>

                            <option value="Other">Other</option>

                        </select>

                    </div>

                </div>



                <div class="form-group">

                    <label for="customerNotes">Notes (Optional)</label>

                    <textarea id="customerNotes"

                              placeholder="Any special requests or notes..."></textarea>

                </div>



                <button type="submit" class="btn btn-primary" id="submitBtn">

                    ✅ Confirm Booking

                </button>

            </form>

        </div>

    </div>



    <!-- APPOINTMENT SCHEDULE -->

    <div id="schedule" class="tab-content">

        <div class="card">

            <div class="card-title">

                <div class="icon">📋</div>

                Appointment Schedule

            </div>



            <div class="filter-bar">

                <input type="text" id="searchInput"

                       placeholder="🔍 Search by name..."

                       oninput="renderSchedule()">



                <select id="filterService" onchange="renderSchedule()">

                    <option value="">All Services</option>

                    <option value="Consultation">Consultation</option>

                    <option value="Haircut">Haircut</option>

                    <option value="Massage">Massage</option>

                    <option value="Dental Checkup">Dental Checkup</option>

                    <option value="Facial">Facial</option>

                    <option value="Manicure">Manicure</option>

                    <option value="Physical Therapy">Physical Therapy</option>

                    <option value="Other">Other</option>

                </select>



                <select id="filterStatus" onchange="renderSchedule()">

                    <option value="">All Status</option>

                    <option value="upcoming">Upcoming</option>

                    <option value="completed">Completed</option>

                    <option value="cancelled">Cancelled</option>

                </select>

            </div>



            <div id="scheduleContainer">

                <div class="empty-state">

                    <h3>Loading appointments...</h3>

                </div>

            </div>

        </div>

    </div>

</div>



<!-- TOAST NOTIFICATION -->

<div class="toast" id="toast">

    <span id="toastIcon">✅</span>

    <span id="toastMessage">Booking confirmed!</span>

</div>



<script>

    // ===================== CONFIGURATION =====================



    const BOOKINGS_API = 'api/bookings.php';

    const CANCEL_API = 'api/cancel_booking.php';



    let bookings = [];

    let currentDate = new Date();

    let selectedDate = null;

    let selectedTime = null;

    let isSubmitting = false;



    const timeSlots = [

        '08:00 AM', '09:00 AM', '10:00 AM', '11:00 AM',

        '12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM',

        '04:00 PM', '05:00 PM', '06:00 PM', '07:00 PM'

    ];



    // ===================== API HELPERS =====================



    async function apiRequest(url, options = {}) {

        const response = await fetch(url, {

            ...options,

            headers: {

                'Accept': 'application/json',

                ...(options.body ? {

                    'Content-Type': 'application/json'

                } : {}),

                ...options.headers

            }

        });



        let result;



        try {

            result = await response.json();

        } catch {

            throw new Error('The server returned an invalid response.');

        }



        if (!response.ok || result.success === false) {

            throw new Error(result.message || 'Request failed.');

        }



        return result;

    }



    async function loadBookings() {

        try {

            const result = await apiRequest(BOOKINGS_API);



            // Supports either a direct array or { success: true, data: [] }.

            bookings = Array.isArray(result)

                ? result

                : (result.data || result.bookings || []);



            // Normalize the fields returned by PHP.

            bookings = bookings.map(b => ({

                ...b,

                id: Number(b.id),

                name: b.name ?? b.customer_name ?? '',

                phone: b.phone ?? b.customer_phone ?? '',

                email: b.email ?? b.customer_email ?? '',

                service: b.service ?? b.service_type ?? '',

                notes: b.notes ?? '',

                date: String(b.date ?? b.booking_date ?? '').slice(0, 10),

                time: b.time ?? b.booking_time ?? '',

                status: b.status ?? 'upcoming'

            }));



            updateStats();

            renderCalendar();

            renderSchedule();

        } catch (error) {

            console.error(error);

            showToast(error.message || 'Could not load bookings.', 'error');



            document.getElementById('scheduleContainer').innerHTML = \`

                <div class="empty-state">

                    <div class="icon">⚠️</div>

                    <h3>Could not load appointments</h3>

                    <p>Check your PHP API and database connection.</p>

                    <button class="btn btn-primary"

                            onclick="loadBookings()">Try Again</button>

                </div>

            \`;

        }

    }



    // ===================== TABS =====================



    function switchTab(tab, button) {

        document.querySelectorAll('.tab-content')

            .forEach(element => element.classList.remove('active'));



        document.querySelectorAll('.tab-btn')

            .forEach(element => element.classList.remove('active'));



        document.getElementById(tab).classList.add('active');

        button.classList.add('active');



        if (tab === 'schedule') {

            renderSchedule();

        }

    }



    // ===================== CALENDAR =====================



    function formatDate(date) {

        const year = date.getFullYear();

        const month = String(date.getMonth() + 1).padStart(2, '0');

        const day = String(date.getDate()).padStart(2, '0');



        return \`${year}-${month}-${day}\`;

    }



    function renderCalendar() {

        const year = currentDate.getFullYear();

        const month = currentDate.getMonth();

        const firstDay = new Date(year, month, 1).getDay();

        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const today = new Date();



        const monthNames = [

            'January', 'February', 'March', 'April', 'May', 'June',

            'July', 'August', 'September', 'October', 'November', 'December'

        ];



        document.getElementById('currentMonth').textContent =

            \`${monthNames[month]} ${year}\`;



        const calendar = document.getElementById('calendar');

        calendar.innerHTML = '';



        ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

            .forEach(day => {

                calendar.innerHTML += \`

                    <div class="day-label">${day}</div>

                \`;

            });



        for (let i = 0; i < firstDay; i++) {

            calendar.innerHTML += \`

                <div class="day-cell disabled"></div>

            \`;

        }



        for (let day = 1; day <= daysInMonth; day++) {

            const date = new Date(year, month, day);

            const dateStr = formatDate(date);



            const isToday =

                day === today.getDate() &&

                month === today.getMonth() &&

                year === today.getFullYear();



            const isSelected = selectedDate === dateStr;



            const hasBooking = bookings.some(b =>

                b.date === dateStr && b.status !== 'cancelled'

            );



            const isPast = dateStr < formatDate(today);



            let classes = 'day-cell';



            if (isToday) classes += ' today';

            if (isSelected) classes += ' selected';

            if (isPast) classes += ' disabled';



            calendar.innerHTML += \`

                <div class="${classes}"

                     ${isPast ? '' : \`onclick="selectDate('${dateStr}')"\`}>

                    ${day}

                    ${hasBooking ? '<div class="booking-dot"></div>' : ''}

                </div>

            \`;

        }

    }



    function changeMonth(delta) {

        currentDate.setDate(1);

        currentDate.setMonth(currentDate.getMonth() + delta);

        renderCalendar();

    }



    function selectDate(dateStr) {

        if (dateStr < formatDate(new Date())) {

            showToast('Please select today or a future date.', 'error');

            return;

        }



        selectedDate = dateStr;

        selectedTime = null;



        renderCalendar();

        renderTimeSlots();



        document.getElementById('timeCard').style.display = 'block';

        document.getElementById('formCard').style.display = 'none';

        document.getElementById('selectedDateDisplay').textContent = dateStr;

    }



    // ===================== TIME SLOTS =====================



    function renderTimeSlots() {

        const container = document.getElementById('timeSlots');

        container.innerHTML = '';



        timeSlots.forEach(time => {

            const isBooked = bookings.some(b =>

                b.date === selectedDate &&

                b.time === time &&

                b.status !== 'cancelled'

            );



            let classes = 'time-slot';



            if (isBooked) classes += ' booked';

            else if (selectedTime === time) classes += ' selected';



            const isPastTime = selectedDate === formatDate(new Date()) &&

                timeToMinutes(time) <=

                (new Date().getHours() * 60 + new Date().getMinutes());



            if (!isBooked && !isPastTime) {

                container.innerHTML += \`

                    <div class="${classes}"

                         onclick="selectTime('${time}')">

                        ${time}

                    </div>

                \`;

            } else {

                container.innerHTML += \`

                    <div class="${classes} booked">

                        ${time}

                    </div>

                \`;

            }

        });

    }



    function timeToMinutes(time) {

        const [clock, period] = time.split(' ');

        let [hours, minutes] = clock.split(':').map(Number);



        if (period === 'PM' && hours !== 12) hours += 12;

        if (period === 'AM' && hours === 12) hours = 0;



        return hours * 60 + minutes;

    }



    function selectTime(time) {

        selectedTime = time;



        renderTimeSlots();



        document.getElementById('formCard').style.display = 'block';

        document.getElementById('sumDate').textContent = selectedDate;

        document.getElementById('sumTime').textContent = selectedTime;



        document.getElementById('bookingForm').scrollIntoView({

            behavior: 'smooth',

            block: 'center'

        });

    }



    // ===================== CREATE BOOKING =====================



    async function submitBooking(event) {

        event.preventDefault();



        if (isSubmitting) return;



        if (!selectedDate || !selectedTime) {

            showToast('Please select a date and time.', 'error');

            return;

        }



        if (selectedDate < formatDate(new Date())) {

            showToast('Please select a future appointment.', 'error');

            return;

        }



        const duplicate = bookings.some(b =>

            b.date === selectedDate &&

            b.time === selectedTime &&

            b.status !== 'cancelled'

        );



        if (duplicate) {

            showToast('This time slot is already booked.', 'error');

            await loadBookings();

            return;

        }



        const booking = {

            name: document.getElementById('customerName').value.trim(),

            phone: document.getElementById('customerPhone').value.trim(),

            email: document.getElementById('customerEmail').value.trim(),

            service: document.getElementById('serviceType').value,

            notes: document.getElementById('customerNotes').value.trim(),

            date: selectedDate,

            time: selectedTime

        };



        if (!booking.name || !booking.phone || !booking.service) {

            showToast('Please fill in all required fields.', 'error');

            return;

        }



        isSubmitting = true;



        const submitBtn = document.getElementById('submitBtn');

        submitBtn.disabled = true;

        submitBtn.textContent = 'Saving booking...';



        try {

            await apiRequest(BOOKINGS_API, {

                method: 'POST',

                body: JSON.stringify(booking)

            });



            showToast('Booking confirmed successfully!', 'success');



            document.getElementById('bookingForm').reset();

            selectedDate = null;

            selectedTime = null;



            document.getElementById('timeCard').style.display = 'none';

            document.getElementById('formCard').style.display = 'none';



            await loadBookings();

        } catch (error) {

            console.error(error);

            showToast(error.message || 'Could not save booking.', 'error');

        } finally {

            isSubmitting = false;

            submitBtn.disabled = false;

            submitBtn.innerHTML = '✅ Confirm Booking';

        }

    }



    // ===================== APPOINTMENT SCHEDULE =====================



    function escapeHtml(value) {

        return String(value ?? '').replace(/[&<>"']/g, char => ({

            '&': '&amp;',

            '<': '&lt;',

            '>': '&gt;',

            '"': '&quot;',

            "'": '&#039;'

        })[char]);

    }



    function renderSchedule() {

        const search = document.getElementById('searchInput').value.toLowerCase();

        const serviceFilter = document.getElementById('filterService').value;

        const statusFilter = document.getElementById('filterStatus').value;



        let filtered = bookings.filter(b => {

            const matchSearch =

                b.name.toLowerCase().includes(search) ||

                b.phone.toLowerCase().includes(search) ||

                b.email.toLowerCase().includes(search);



            const matchService = !serviceFilter || b.service === serviceFilter;

            const matchStatus = !statusFilter || b.status === statusFilter;



            return matchSearch && matchService && matchStatus;

        });



        filtered.sort((a, b) =>

            (a.date + ' ' + a.time).localeCompare(b.date + ' ' + b.time)

        );



        const container = document.getElementById('scheduleContainer');



        if (filtered.length === 0) {

            container.innerHTML = \`

                <div class="empty-state">

                    <div class="icon">📭</div>

                    <h3>No appointments found</h3>

                    <p>Try adjusting your filters or book a new appointment.</p>

                </div>

            \`;

            return;

        }



        let html = \`

            <table class="schedule-table">

                <thead>

                    <tr>

                        <th>Customer</th>

                        <th>Service</th>

                        <th>Date & Time</th>

                        <th>Contact</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>

                <tbody>

        \`;



        filtered.forEach(b => {

            const statusClass = b.status === 'upcoming'

                ? 'status-upcoming'

                : b.status === 'completed'

                    ? 'status-completed'

                    : 'status-cancelled';



            const statusText = b.status.charAt(0).toUpperCase()

                + b.status.slice(1);



            html += \`

                <tr>

                    <td>

                        <div class="customer-name">

                            ${escapeHtml(b.name)}

                        </div>

                        ${b.notes ? \`

                            <div class="customer-service">

                                📝 ${escapeHtml(

                                    b.notes.length > 30

                                        ? b.notes.substring(0, 30) + '...'

                                        : b.notes

                                )}

                            </div>

                        \` : ''}

                    </td>



                    <td>${escapeHtml(b.service)}</td>



                    <td class="date-time">

                        ${escapeHtml(b.date)}<br>

                        <small>${escapeHtml(b.time)}</small>

                    </td>



                    <td>

                        <div>${escapeHtml(b.phone)}</div>

                        ${b.email ? \`

                            <small style="color:var(--text-light)">

                                ${escapeHtml(b.email)}

                            </small>

                        \` : ''}

                    </td>



                    <td>

                        <span class="status-badge ${statusClass}">

                            ${escapeHtml(statusText)}

                        </span>

                    </td>



                    <td>

                        ${b.status === 'upcoming' ? \`

                            <button class="btn btn-danger"

                                    onclick="cancelBooking(${b.id})">

                                Cancel

                            </button>

                        \` : \`

                            <span style="color:var(--text-light);font-size:0.85rem">

                                —

                            </span>

                        \`}

                    </td>

                </tr>

            \`;

        });



        html += '</tbody></table>';

        container.innerHTML = html;

    }



    // ===================== CANCEL BOOKING =====================



    async function cancelBooking(id) {

        if (!confirm('Are you sure you want to cancel this appointment?')) {

            return;

        }



        try {

            await apiRequest(CANCEL_API, {

                method: 'POST',

                body: JSON.stringify({ id: id })

            });



            showToast('Appointment cancelled successfully.', 'error');

            await loadBookings();

        } catch (error) {

            console.error(error);

            showToast(error.message || 'Could not cancel appointment.', 'error');

        }

    }



    // ===================== STATISTICS =====================



    function updateStats() {

        const today = formatDate(new Date());



        document.getElementById('stat-total').textContent =

            bookings.filter(b => b.status !== 'cancelled').length;



        document.getElementById('stat-today').textContent =

            bookings.filter(b =>

                b.date === today && b.status !== 'cancelled'

            ).length;



        document.getElementById('stat-upcoming').textContent =

            bookings.filter(b => b.status === 'upcoming').length;

    }



    // ===================== TOAST =====================



    function showToast(message, type) {

        const toast = document.getElementById('toast');



        document.getElementById('toastMessage').textContent = message;

        document.getElementById('toastIcon').textContent =

            type === 'success' ? '✅' : '❌';



        toast.className = \`toast ${type} show\`;



        setTimeout(() => {

            toast.classList.remove('show');

        }, 3000);

    }



    // ===================== INITIALIZE =====================



    renderCalendar();

    loadBookings();

</script>



</body>

</html>



