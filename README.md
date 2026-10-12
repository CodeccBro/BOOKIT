# BookIt — PHP + MySQL

This adds a small PHP/PDO API and MySQL storage to your existing BookIt HTML/CSS/JavaScript interface.

## Setup (XAMPP)
1. Copy this `bookit_php_mysql` folder into `C:\xampp\htdocs\`.
2. In phpMyAdmin, create/import the database by importing `database/bookit.sql`.
3. Open `config/database.php` and confirm your MySQL username/password. XAMPP defaults are usually `root` and an empty password.
4. Copy your existing BookIt page into this folder and rename it `index.php`. Keep your existing `style.css`.
5. Apply the JavaScript changes under **Connect your existing JavaScript** below.
6. Start Apache and MySQL in XAMPP and visit `http://localhost/bookit_php_mysql/`.

## Connect your existing JavaScript

In `index.php`, replace:
```js
let bookings = JSON.parse(localStorage.getItem('bookit_bookings')) || [];
```
with:
```js
let bookings = [];
async function loadBookings() {
    const response = await fetch('api/bookings.php');
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || 'Could not load bookings');
    bookings = result.data;
    renderCalendar();
    updateStats();
    if (document.getElementById('schedule').classList.contains('active')) renderSchedule();
}
```

Replace your `submitBooking(e)` with the async version below:
```js
async function submitBooking(e) {
    e.preventDefault();
    if (!selectedDate || !selectedTime) {
        showToast('Please select a date and time first.', 'error');
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

    try {
        const response = await fetch('api/bookings.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(booking)
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Booking failed');

        await loadBookings();
        showToast('✅ Booking confirmed successfully!', 'success');
        document.getElementById('bookingForm').reset();
        selectedDate = null;
        selectedTime = null;
        document.getElementById('timeCard').style.display = 'none';
        document.getElementById('formCard').style.display = 'none';
        renderCalendar();
    } catch (error) {
        showToast(error.message || 'Could not save booking.', 'error');
        await loadBookings();
        renderTimeSlots();
    }
}
```

Replace `cancelBooking(id)` with:
```js
async function cancelBooking(id) {
    if (!confirm('Are you sure you want to cancel this appointment?')) return;

    try {
        const response = await fetch('api/cancel_booking.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id})
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Cancellation failed');

        await loadBookings();
        renderSchedule();
        renderCalendar();
        showToast('❌ Appointment cancelled', 'error');
    } catch (error) {
        showToast(error.message || 'Could not cancel appointment.', 'error');
    }
}
```

Remove the old `saveBookings()` function (or leave it unused). At the bottom, replace:
```js
renderCalendar();
updateStats();
```
with:
```js
loadBookings().catch(error => {
    console.error(error);
    showToast('Could not load bookings. Check your PHP/MySQL setup.', 'error');
});
```

Your existing schedule/calendar rendering functions can stay. The API returns IDs as numbers and uses the same field names as your JavaScript (`name`, `phone`, `email`, `service`, `notes`, `date`, `time`, `status`).

## Important
- Run this through `http://localhost/...`, not by double-clicking the HTML file.
- Do not upload database credentials to a public Git repository.
- This is a starter setup. Add authentication and authorization before exposing staff schedule/cancellation tools publicly.
