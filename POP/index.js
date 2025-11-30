// ✅ ОБЪЕДИНЁННЫЙ JS КОД БЕЗ КОНФЛИКТОВ
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация всех форм и функций
    initializeRegistrationForm();
    initializeReviewForm();
    initializeFeedbackForm();
    initializeSmoothScrolling();
    initializeAnimations();
    initializeFileUploads();
    initializePhoneMask();
});

// ==================== РЕГИСТРАЦИЯ ====================
function initializeRegistrationForm() {
    const registrationForm = document.getElementById('registration-form');
    if (!registrationForm) return;

    registrationForm.addEventListener('submit', handleRegistrationSubmit);
    
    // Валидация в реальном времени
    const inputs = registrationForm.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', clearFieldError);
    });
}

async function handleRegistrationSubmit(e) {
    e.preventDefault();
    
    if (!validateForm()) return;

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.innerHTML = '<div class="loading-spinner"></div> Отправка...';
    submitBtn.disabled = true;

    await submitRegistrationForm(e.target);
    
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
}

// ==================== ОТЗЫВЫ ====================
function initializeReviewForm() {
    const reviewForm = document.querySelector('.review-form');
    if (!reviewForm) return;

    reviewForm.addEventListener('submit', handleReviewSubmit);
    
    // Инициализация звездочек
    initializeStars(reviewForm);
}

async function handleReviewSubmit(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('.btn-submit');
    const originalText = submitBtn.textContent;
    submitBtn.innerHTML = '<div class="loading-spinner"></div> Отправка...';
    submitBtn.disabled = true;

    await submitGenericForm(e.target, 'save_review.php', 'review-message');
    
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
}

// ==================== ОБРАТНАЯ СВЯЗЬ ====================
function initializeFeedbackForm() {
    const feedbackForm = document.querySelector('.contact-card form');
    if (!feedbackForm) return;

    feedbackForm.id = 'feedback-form'; // Добавляем ID для совместимости
    feedbackForm.addEventListener('submit', handleFeedbackSubmit);
}

async function handleFeedbackSubmit(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('.submit-btn');
    const originalText = submitBtn.textContent;
    submitBtn.innerHTML = '<div class="loading-spinner"></div> Отправка...';
    submitBtn.disabled = true;

    await submitGenericForm(e.target, 'save_feedback.php', 'feedback-message');
    
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
}

// ==================== УНИВЕРСАЛЬНАЯ ОТПРАВКА ФОРМ ====================
async function submitGenericForm(form, phpFile, messageId) {
    const formData = new FormData(form);
    const messageDiv = document.getElementById(messageId);
    
    if (messageDiv) messageDiv.innerHTML = '';

    try {
        const response = await fetch(phpFile, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            form.reset();
            if (messageDiv) {
                messageDiv.innerHTML = `<div class="success-msg">${result.message}</div>`;
            }
        } else {
            showNotification(result.error, 'error');
            if (messageDiv) {
                messageDiv.innerHTML = `<div class="error-msg">${result.error}</div>`;
            }
        }
    } catch (error) {
        showNotification('Ошибка сети', 'error');
        if (messageDiv) {
            messageDiv.innerHTML = `<div class="error-msg">Ошибка сети: ${error.message}</div>`;
        }
    }
}

async function submitRegistrationForm(form) {
    const formData = new FormData(form);
    const progressContainer = form.querySelector('.upload-progress') || createProgressBar(form);
    const progressFill = progressContainer.querySelector('.progress-fill');
    const progressText = progressContainer.querySelector('.progress-text');
    const messageDiv = document.getElementById('form-message');

    progressContainer.style.display = 'block';
    progressFill.style.width = '0%';
    if (progressText) progressText.textContent = '0%';
    if (messageDiv) messageDiv.innerHTML = '';

    try {
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressFill.style.width = percent + '%';
                if (progressText) progressText.textContent = percent + '%';
            }
        });
        
        await new Promise((resolve, reject) => {
            xhr.addEventListener('load', function() {
                progressContainer.style.display = 'none';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        showNotification(response.message, 'success');
                        form.reset();
                        clearFilePreviews();
                        
                        if (messageDiv) {
                            messageDiv.innerHTML = `
                                <div class="success-msg">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 20px;">✅</span>
                                        <div>
                                            <strong>${response.message}</strong><br>
                                            <small>Ваш ID: ${response.participant_id || 'N/A'}</small>
                                        </div>
                                    </div>
                                </div>
                            `;
                            messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        resolve();
                    } else {
                        showNotification(response.error, 'error');
                        if (messageDiv) messageDiv.innerHTML = `<div class="error-msg">${response.error}</div>`;
                        reject(new Error(response.error));
                    }
                } catch (parseError) {
                    showNotification('Ошибка сервера', 'error');
                    if (messageDiv) messageDiv.innerHTML = `<div class="error-msg">Ошибка сервера</div>`;
                    reject(new Error('JSON parse error'));
                }
            });
            
            xhr.addEventListener('error', function() {
                progressContainer.style.display = 'none';
                showNotification('Ошибка сети', 'error');
                if (messageDiv) messageDiv.innerHTML = `<div class="error-msg">Ошибка сети</div>`;
                reject(new Error('Network error'));
            });

            xhr.open('POST', 'register.php');
            xhr.send(formData);
        });
        
    } catch (error) {
        progressContainer.style.display = 'none';
        console.error('Submit error:', error);
    }
}

// ==================== ВАЛИДАЦИЯ ====================
function validateForm() {
    let isValid = true;
    const form = document.getElementById('registration-form');
    
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!validateField({ target: field })) {
            isValid = false;
        }
    });

    const categories = form.querySelectorAll('input[name="categories[]"]:checked');
    if (categories.length === 0) {
        showFieldError(form.querySelector('.categories-fieldset'), 'Выберите хотя бы одну категорию');
        isValid = false;
    }

    return isValid;
}

function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    clearFieldError(e);

    // Логика валидации (как в оригинале)
    switch(field.type) {
        case 'text':
            if (field.name === 'full_name') {
                if (value.length < 2) {
                    errorMessage = 'ФИО должно содержать минимум 2 символа';
                    isValid = false;
                } else if (!/^[а-яА-ЯёЁ\s-]+$/.test(value)) {
                    errorMessage = 'ФИО должно содержать только русские буквы';
                    isValid = false;
                }
            }
            break;
        case 'tel':
            const cleanPhone = value.replace(/\D/g, '');
            if (cleanPhone.length < 10) {
                errorMessage = 'Введите корректный номер телефона';
                isValid = false;
            }
            break;
        case 'number':
            const age = parseInt(value);
            if (age < 16 || age > 100) {
                errorMessage = 'Возраст должен быть от 16 до 100 лет';
                isValid = false;
            }
            break;
        case 'file':
            // Валидация файлов (как в оригинале)
            if (field.required && (!field.files || field.files.length === 0)) {
                errorMessage = 'Это поле обязательно для заполнения';
                isValid = false;
            } else if (field.files[0]) {
                const file = field.files[0];
                const maxSize = field.name === 'photo' ? 5 * 1024 * 1024 : 10 * 1024 * 1024;
                
                if (file.size > maxSize) {
                    errorMessage = `Файл слишком большой. Максимум: ${field.name === 'photo' ? '5MB' : '10MB'}`;
                    isValid = false;
                }
            }
            break;
    }

    if (!isValid) {
        showFieldError(field, errorMessage);
    } else {
        showFieldSuccess(field);
    }

    return isValid;
}

function showFieldError(field, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.cssText = `
        color: #ef4444; font-size: 12px; margin-top: 5px;
        display: flex; align-items: center; gap: 5px;
    `;
    
    let parent = field.closest('.form-group') || field.parentElement;
    parent.appendChild(errorDiv);
    field.style.borderColor = '#ef4444';
}

function showFieldSuccess(field) {
    field.style.borderColor = '#10b981';
}

function clearFieldError(e) {
    const field = e.target;
    let parent = field.closest('.form-group') || field.parentElement;
    let errorDiv = parent.querySelector('.field-error');
    
    if (errorDiv) errorDiv.remove();
    field.style.borderColor = '';
}

// ==================== ФАЙЛЫ И ПРЕВЬЮ ====================
function initializeFileUploads() {
    const photoInput = document.getElementById('photo');
    const musicInput = document.getElementById('music');
    
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            showFilePreviews();
            validateField({ target: this });
        });
    }
    
    if (musicInput) {
        musicInput.addEventListener('change', function() {
            showFilePreviews();
            validateField({ target: this });
        });
    }
}

function showFilePreviews() {
    // Логика превью файлов (как в оригинале)
    const photoInput = document.getElementById('photo');
    const musicInput = document.getElementById('music');
    const photoPreview = document.getElementById('photo-preview');
    const musicPreview = document.getElementById('music-preview');

    if (photoInput?.files[0] && photoPreview) {
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.innerHTML = `
                <div style="position: relative; display: inline-block;">
                    <img src="${e.target.result}" style="max-width:150px; max-height:150px; border-radius:12px; border: 2px solid #10b981;">
                    <button type="button" onclick="clearFilePreview('photo')" style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 12px;">×</button>
                </div>
            `;
        };
        reader.readAsDataURL(photoInput.files[0]);
    }

    if (musicInput?.files[0] && musicPreview) {
        const reader = new FileReader();
        reader.onload = (e) => {
            musicPreview.innerHTML = `
                <div style="position: relative; display: inline-block;">
                    <audio controls style="width:200px; height:40px;">
                        <source src="${e.target.result}" type="${musicInput.files[0].type}">
                    </audio>
                    <button type="button" onclick="clearFilePreview('music')" style="position: absolute; top: -8px; right: -8px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 12px;">×</button>
                </div>
            `;
        };
        reader.readAsDataURL(musicInput.files[0]);
    }
}

function clearFilePreview(type) {
    const input = document.getElementById(type);
    const preview = document.getElementById(`${type}-preview`);
    if (input) input.value = '';
    if (preview) preview.innerHTML = '';
}

function clearFilePreviews() {
    clearFilePreview('photo');
    clearFilePreview('music');
}

function createProgressBar(form) {
    const progressHTML = `
        <div class="upload-progress" style="display: none; margin: 20px 0;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Загрузка...</span>
                <span class="progress-text">0%</span>
            </div>
            <div class="progress-bar" style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                <div class="progress-fill" style="height: 100%; background: #10b981; width: 0%; transition: width 0.3s ease;"></div>
            </div>
        </div>
    `;
    form.insertAdjacentHTML('beforeend', progressHTML);
    return form.querySelector('.upload-progress');
}

// ==================== ДОПОЛНИТЕЛЬНЫЕ ФУНКЦИИ ====================
function initializeStars(form) {
    const stars = form.querySelector('.stars');
    if (!stars) return;
    
    stars.addEventListener('change', function() {
        const checkedStar = stars.querySelector('input:checked');
        if (checkedStar) {
            const rating = checkedStar.value;
            // Подсвечиваем звезды
            Array.from(stars.children).forEach((star, index) => {
                if (index < rating) {
                    star.style.color = '#ff5a7b';
                } else {
                    star.style.color = '#ddd';
                }
            });
        }
    });
}

function initializePhoneMask() {
    const phoneInput = document.getElementById('phone');
    if (!phoneInput) return;
    
    phoneInput.addEventListener('input', function(e) {
        // Сохраняем позицию курсора ДО изменений
        const cursorPosition = phoneInput.selectionStart;
        const oldLength = phoneInput.value.length;
        
        // Извлекаем только цифры
        let numbers = phoneInput.value.replace(/\D/g, '');
        
        // Убираем 7 или 8 в начале
        if (numbers.startsWith('7') || numbers.startsWith('8')) {
            numbers = numbers.substring(1);
        }
        
        // Ограничиваем до 10 цифр
        numbers = numbers.substring(0, 10);
        
        // Формируем маску +7 (XXX) XXX-XX-XX
        let formattedValue = '+7 ';
        if (numbers.length > 0) {
            formattedValue += '(' + numbers.substring(0, 3);
        }
        if (numbers.length > 3) {
            formattedValue += ') ' + numbers.substring(3, 6);
        }
        if (numbers.length > 6) {
            formattedValue += '-' + numbers.substring(6, 8);
        }
        if (numbers.length > 8) {
            formattedValue += '-' + numbers.substring(8, 10);
        }
        
        // Устанавливаем новое значение
        phoneInput.value = formattedValue;
        
        // ✅ ВОТ КЛЮЧЕВОЕ ИСПРАВЛЕНИЕ - правильно ставим курсор
        let newCursorPosition = cursorPosition;
        
        // Корректируем позицию курсора в зависимости от добавленных символов
        const addedChars = formattedValue.length - oldLength;
        if (addedChars > 0) {
            newCursorPosition += addedChars;
        }
        
        // Ограничиваем позицию концом строки
        newCursorPosition = Math.min(newCursorPosition, formattedValue.length);
        
        // Устанавливаем курсор в правильное место
        phoneInput.setSelectionRange(newCursorPosition, newCursorPosition);
    });
    
    // Предотвращаем вставку нецифровых символов
    phoneInput.addEventListener('keydown', function(e) {
        // Разрешаем: цифры, Backspace, Delete, стрелки, Tab, Escape
        if (e.key.length === 1 && !/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
}

function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerHeight = document.querySelector('.top-header')?.offsetHeight || 0;
                window.scrollTo({
                    top: target.offsetTop - headerHeight - 20,
                    behavior: 'smooth'
                });
            }
        });
    });
}

function initializeAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.why-card, .category, .step, .review-card, .gallery img').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 15px 20px;
        border-radius: 8px; color: white; font-weight: 500; z-index: 10000;
        transform: translateX(400px); transition: transform 0.3s ease;
        max-width: 300px; background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
    `;
    
    document.body.appendChild(notification);
    setTimeout(() => notification.style.transform = 'translateX(0)', 100);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// ==================== СТИЛИ ====================
const styles = document.createElement('style');
styles.textContent = `
    .loading-spinner {
        display: inline-block; width: 16px; height: 16px;
        border: 2px solid #ffffff; border-radius: 50%; border-top-color: transparent;
        animation: spin 1s ease-in-out infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .field-error::before { content: "⚠"; font-size: 14px; }
    .success-msg { background: #ecfdf5; border-left: 4px solid #10b981; color: #065f46; padding: 15px; border-radius: 8px; margin: 10px 0; }
    .error-msg { background: #fef2f2; border-left: 4px solid #ef4444; color: #7f1d1d; padding: 15px; border-radius: 8px; margin: 10px 0; }
`;
document.head.appendChild(styles);
