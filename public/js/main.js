// Main JavaScript for Student Grade & Attendance System

// CSRF Token helper
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// AJAX helper
async function fetchAPI(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
        }
    };

    const response = await fetch(url, { ...defaultOptions, ...options });
    const data = await response.json();

    if (!response.ok) {
        throw new Error(data.message || 'เกิดข้อผิดพลาด');
    }

    return data;
}

// Show alert message
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;

    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Confirm delete
function confirmDelete(message = 'คุณแน่ใจหรือไม่ที่จะลบข้อมูลนี้?') {
    return confirm(message);
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

// Auto-save grade (debounced)
let gradeTimeout;
function autoSaveGrade(studentId, courseId, categoryId, score) {
    clearTimeout(gradeTimeout);

    console.log('🔄 Saving grade:', { studentId, courseId, categoryId, score });

    gradeTimeout = setTimeout(async () => {
        try {
            const formData = new FormData();
            formData.append('student_id', studentId);
            formData.append('course_id', courseId);
            formData.append('category_id', categoryId);
            formData.append('score', score);
            formData.append('csrf_token', getCsrfToken());

            console.log('📤 Sending request to /api/save-grade');

            const response = await fetch('/api/save-grade', {
                method: 'POST',
                body: formData
            });

            console.log('📥 Response status:', response.status);

            const data = await response.json();
            console.log('📦 Response data:', data);

            if (data.success) {
                console.log('✅ บันทึกคะแนนสำเร็จ');
                // Show brief success indicator
                // Note: 'event' is not passed to autoSaveGrade, so event.target will be undefined here.
                // You might need to pass the input element directly to this function if you want to use it.
                const input = event?.target;
                if (input) {
                    input.style.borderColor = '#10b981';
                    setTimeout(() => { input.style.borderColor = ''; }, 1000);
                }
            } else {
                console.error('❌ Error:', data.message);
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        } catch (error) {
            console.error('❌ เกิดข้อผิดพลาด:', error);
            alert('ไม่สามารถบันทึกคะแนนได้: ' + error.message);
        }
    }, 1000);
}

// Alias for compatibility
function saveGrade(studentId, courseId, categoryId, score) {
    autoSaveGrade(studentId, courseId, categoryId, score);
}

// Auto-save attendance
async function saveAttendance(studentId, courseId, date, status) {
    try {
        const formData = new FormData();
        formData.append('student_id', studentId);
        formData.append('course_id', courseId);
        formData.append('date', date);
        formData.append('status', status);
        formData.append('csrf_token', getCsrfToken());

        const response = await fetch('/api/save-attendance', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            showAlert('บันทึกการเข้าเรียนสำเร็จ', 'success');
        }
    } catch (error) {
        showAlert('เกิดข้อผิดพลาด: ' + error.message, 'error');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    // Add event listeners for delete buttons
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirmDelete()) {
                e.preventDefault();
            }
        });
    });

    // File upload preview
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function (e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                const label = document.querySelector('.file-label');
                if (label) {
                    label.textContent = fileName;
                }
            }
        });
    }
});
