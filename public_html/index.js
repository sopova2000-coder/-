// ----------------- ИНИЦИАЛИЗАЦИЯ -----------------
document.addEventListener('DOMContentLoaded', () => {
    initForms();
    initAnimations();
    initStars();
});

function initForms() {
    initRegistration();
    initReview();
    initFeedback();
    initFiles();
    initPhoneMask();
}

// ----------------- МАСКА ТЕЛЕФОНА -----------------
function initPhoneMask() {
    const phones = document.querySelectorAll('#phone, [name="phone"]');
    phones.forEach(phone => {
        phone.addEventListener('input', function () {
            let digits = this.value.replace(/\D/g, '');
            if (digits.length > 11) digits = digits.slice(0, 11);

            let formatted = '+7 ';
            if (digits.length > 1) {
                formatted += `(${digits.slice(1, 4)}) `;
                if (digits.length > 4) {
                    formatted += `${digits.slice(4, 7)}`;
                    if (digits.length > 7) {
                        formatted += `-${digits.slice(7, 9)}`;
                        if (digits.length > 9) {
                            formatted += `-${digits.slice(9, 11)}`;
                        }
                    }
                }
            }
            this.value = formatted;
        });
    });
}

// ----------------- РЕГИСТРАЦИЯ -----------------
function initRegistration() {
    const form = document.getElementById('registration-form');
    if (!form) return;

    form.addEventListener('submit', e => {
        e.preventDefault();
        clearAllErrors(form);
        if (validateRegistration(form)) {
            submitRegistration(form);
        }
    });
}

function validateRegistration(form) {
    let valid = true;

    const name = form.querySelector('#full_name');
    if (name && (!name.value.trim() || name.value.length < 2)) {
        showError(name, 'ФИО: минимум 2 символа');
        valid = false;
    }

    const phone = form.querySelector('#phone');
    if (phone) {
        const digits = phone.value.replace(/\D/g, '');
        if (digits.length < 10) {
            showError(phone, 'Телефон: минимум 10 цифр');
            valid = false;
        }
    }

    const age = form.querySelector('#age');
    if (age) {
        const n = parseInt(age.value, 10);
        if (isNaN(n) || n < 16 || n > 100) {
            showError(age, 'Возраст: 16–100 лет');
            valid = false;
        }
    }

    const photo = form.querySelector('#photo');
    if (photo && !photo.files.length) {
        showError(photo, 'Выберите фото');
        valid = false;
    }

    return valid;
}

function submitRegistration(form) {
    const btn = form.querySelector('button[type="submit"]');
    const original = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '⏳ Отправка...';

    const formData = new FormData(form);

    fetch('register.php', {
        method: 'POST',
        body: formData
    })
        .then(resp => {
            if (!resp.ok) throw new Error('Сервер: ' + resp.status);
            return resp.text();               // регистр может вернуть строку JSON
        })
        .then(text => {
            console.log('Ответ регистрации:', text);
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                throw new Error('Некорректный JSON от сервера: ' + text.slice(0, 100));
            }

            if (result.success) {
                showNotification(result.message || 'Регистрация успешна');
                form.reset();
                clearAllErrors(form);
            } else {
                showNotification('❌ ' + (result.error || 'Ошибка регистрации'), 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('❌ ' + err.message, 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = original;
        });
}

// ----------------- ОТЗЫВЫ -----------------
function initReview() {
    const form = document.getElementById('review-form');
    if (!form) return;

    form.addEventListener('submit', async e => {
        e.preventDefault();

        const btn = form.querySelector('button[type="submit"]');
        const spinner = btn.querySelector('.loading-spinner');
        const btnText = btn.querySelector('.btn-text');
        const originalText = btnText ? btnText.textContent : 'Отправить отзыв';

        btn.disabled = true;
        if (spinner) spinner.style.display = 'block';
        if (btnText) btnText.textContent = 'Отправляем...';

        const formData = new FormData(form);
        console.log('📤 Отзыв:', Object.fromEntries(formData));

        try {
            const resp = await fetch('admins/reviews_api.php', {
                method: 'POST',
                body: formData
            });

            const text = await resp.text();
            console.log('RAW ответ отзывов:', text.slice(0, 200));

            let result;
            try {
                result = JSON.parse(text);
            } catch {
                throw new Error('Сервер вернул не JSON (возможен редирект/ошибка)');
            }

            if (result.success) {
                showNotification(result.message || 'Спасибо за отзыв!');
                form.reset();
                form.querySelectorAll('.stars label')
                    .forEach(l => (l.style.color = '#e5e7eb'));
            } else {
                showNotification('❌ ' + (result.error || 'Ошибка сервера'), 'error');
            }
        } catch (err) {
            console.error(err);
            showNotification('❌ ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            if (spinner) spinner.style.display = 'none';
            if (btnText) btnText.textContent = originalText;
        }
    });
}

// ----------------- ОБРАТНАЯ СВЯЗЬ -----------------
function initFeedback() {
    const form = document.getElementById('feedback-form');
    if (!form) return;

    form.addEventListener('submit', async e => {
        e.preventDefault();

        const btn = form.querySelector('button[type="submit"]');
        const spinner = btn.querySelector('.loading-spinner');
        const btnText = btn.querySelector('.btn-text');
        const originalText = btnText ? btnText.textContent : 'Отправить сообщение';

        btn.disabled = true;
        if (spinner) spinner.style.display = 'block';
        if (btnText) btnText.textContent = 'Отправляем...';

        const formData = new FormData(form);
        console.log('📤 Feedback:', Object.fromEntries(formData));

        try {
            const resp = await fetch('admins/feedback_api.php', {
                method: 'POST',
                body: formData
            });

            const text = await resp.text();
            console.log('RAW ответ feedback:', text.slice(0, 200));

            let result;
            try {
                result = JSON.parse(text);
            } catch {
                throw new Error('Сервер вернул не JSON (возможен редирект/ошибка)');
            }

            if (result.success) {
                showNotification(result.message || 'Сообщение отправлено!');
                form.reset();
            } else {
                showNotification('❌ ' + (result.error || 'Ошибка сервера'), 'error');
            }
        } catch (err) {
            console.error(err);
            showNotification('❌ ' + err.message, 'error');
        } finally {
            btn.disabled = false;
            if (spinner) spinner.style.display = 'none';
            if (btnText) btnText.textContent = originalText;
        }
    });
}

// ----------------- ФАЙЛЫ + ПРЕВЬЮ -----------------
function initFiles() {
    ['photo', 'music'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;

        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const nameEl = document.getElementById(id + '-name');
            if (nameEl) nameEl.textContent = file.name;

            if (id === 'photo') {
                const preview = document.getElementById('photo-preview');
                if (preview) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.innerHTML =
                            `<img src="${e.target.result}" style="max-width:100%;height:120px;object-fit:cover;border-radius:8px;">`;
                        preview.classList.add('active');
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    });
}

// ----------------- ЗВЁЗДЫ -----------------
function initStars() {
    document.querySelectorAll('.stars').forEach(container => {
        const labels = container.querySelectorAll('label');
        if (!labels.length) return;

        function update(hoverVal = null) {
            const checked = container.querySelector('input[type="radio"]:checked');
            const current = checked ? parseInt(checked.value, 10) : 0;
            const value = hoverVal !== null ? hoverVal : current;

            labels.forEach((label, index) => {
                const starVal = 5 - index;
                label.style.color = starVal <= value ? '#fbbf24' : '#e5e7eb';
            });
        }

        labels.forEach((label, index) => {
            const starVal = 5 - index;

            label.addEventListener('mouseenter', () => update(starVal));
            label.addEventListener('mouseleave', () => update());
            label.addEventListener('click', () => {
                const input = container.querySelector(`input[value="${starVal}"]`);
                if (input) {
                    input.checked = true;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    update(starVal);
                }
            });
        });

        container.querySelectorAll('input[type="radio"]').forEach(input => {
            input.addEventListener('change', () => update());
        });

        update();
    });
}

// ----------------- UI ХЕЛПЕРЫ -----------------
function showError(field, message) {
    clearError(field);
    const err = document.createElement('div');
    err.className = 'field-error';
    err.textContent = message;
    err.style.cssText = 'color:#ef4444;font-size:0.85rem;margin-top:0.25rem;';
    field.parentNode.appendChild(err);
    field.style.borderColor = '#ef4444';
}

function clearError(field) {
    const err = field.parentNode.querySelector('.field-error');
    if (err) err.remove();
    field.style.borderColor = '';
}

function clearAllErrors(form) {
    form.querySelectorAll('.field-error').forEach(e => e.remove());
    form.querySelectorAll('input, select, textarea').forEach(f => {
        f.style.borderColor = '';
    });
}

function showNotification(text, type = 'success') {
    let note = document.getElementById('notification');
    if (!note) {
        note = document.createElement('div');
        note.id = 'notification';
        document.body.appendChild(note);
    }

    note.textContent = text;
    note.style.cssText = `
        position:fixed;top:20px;right:20px;padding:1rem 1.5rem;
        background:${type === 'success' ? '#10b981' : '#ef4444'};
        color:#fff;border-radius:8px;box-shadow:0 10px 25px rgba(0,0,0,0.2);
        transform:translateX(100%);transition:transform .3s;z-index:9999;
    `;

    setTimeout(() => (note.style.transform = 'translateX(0)'), 50);
    setTimeout(() => (note.style.transform = 'translateX(100%)'), 3000);
}

// ----------------- АНИМАЦИИ -----------------
function initAnimations() {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    });

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
}

