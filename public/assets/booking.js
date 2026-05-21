document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.querySelector('[name="service_id"]');
    const specialistSelect = document.querySelector('[name="specialist_id"]');
    const dateInput = document.querySelector('[name="booking_date"]');
    const slotsContainer = document.getElementById('slots-container');
    const hiddenTimeInput = document.querySelector('[name="booking_time"]');
    const priceDisplay = document.getElementById('price-display');
    
    // Кэш специалистов по услуге
    const specialistsCache = {};
    
    // Загрузка специалистов при выборе услуги
    serviceSelect?.addEventListener('change', async function() {
        const serviceId = this.value;
        if (!serviceId) {
            specialistSelect.innerHTML = '<option value="">Сначала выберите услугу</option>';
            return;
        }
        
        specialistSelect.disabled = true;
        specialistSelect.innerHTML = '<option>Загрузка...</option>';
        
        try {
            // Если есть кэш — используем его
            if (specialistsCache[serviceId]) {
                fillSpecialists(specialistsCache[serviceId]);
                return;
            }
            
            const response = await fetch(`index.php?page=api/get_specialists&service_id=${serviceId}`);
            const data = await response.json();
            
            if (data.success) {
                specialistsCache[serviceId] = data.specialists;
                fillSpecialists(data.specialists);
                // Обновляем цену и длительность
                const service = data.services?.find(s => s.service_id == serviceId);
                if (service) {
                    document.getElementById('duration-display').textContent = service.duration + ' мин';
                    priceDisplay.textContent = new Intl.NumberFormat('ru-RU').format(service.price) + ' ₽';
                }
            }
        } catch (e) {
            specialistSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
            console.error(e);
        } finally {
            specialistSelect.disabled = false;
        }
    });
    
    function fillSpecialists(specialists) {
        specialistSelect.innerHTML = '<option value="">Выберите специалиста</option>';
        specialists.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.specialist_id;
            opt.textContent = `${s.last_name} ${s.first_name} — ${s.specialization}`;
            specialistSelect.appendChild(opt);
        });
    }
    
    // Загрузка слотов при выборе даты и специалиста
    async function loadSlots() {
        const serviceId = serviceSelect?.value;
        const specialistId = specialistSelect?.value;
        const date = dateInput?.value;
        
        if (!serviceId || !specialistId || !date) {
            slotsContainer.innerHTML = '<p class="text-muted">Выберите услугу, специалиста и дату</p>';
            return;
        }
        
        slotsContainer.innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
        
        try {
            const response = await fetch(
                `api/get_available_slots.php?service_id=${serviceId}&specialist_id=${specialistId}&date=${date}`
            );
            const data = await response.json();
            
            if (data.success && data.slots.length > 0) {
                slotsContainer.innerHTML = data.slots.map(time => 
                    `<button type="button" class="btn btn-outline-primary slot-btn" data-time="${time}">${time}</button>`
                ).join(' ');
                
                // Обработчик выбора слота
                document.querySelectorAll('.slot-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('active', 'btn-primary'));
                        this.classList.add('active', 'btn-primary');
                        hiddenTimeInput.value = this.dataset.time;
                    });
                });
            } else {
                slotsContainer.innerHTML = '<p class="text-warning">⚠️ Нет свободного времени на выбранную дату</p>';
            }
        } catch (e) {
            slotsContainer.innerHTML = '<p class="text-danger">Ошибка загрузки слотов</p>';
            console.error(e);
        }
    }
    
    specialistSelect?.addEventListener('change', loadSlots);
    dateInput?.addEventListener('change', loadSlots);
    
    // Ограничение даты: не раньше завтра
    if (dateInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.min = tomorrow.toISOString().split('T')[0];
    }
    
    // AJAX обновление статуса в списке записей
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', async function() {
            const bookingId = this.dataset.id;
            const newStatus = this.value;
            const original = this.dataset.original;
            
            try {
                const formData = new FormData();
                formData.append('booking_id', bookingId);
                formData.append('status', newStatus);
                formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
                
                const response = await fetch('index.php?page=bookings/update_status', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (!result.success) {
                    alert('Ошибка: ' + result.error);
                    this.value = original; // откат
                } else {
                    // Обновляем визуальный статус
                    const badge = this.closest('tr')?.querySelector('.status-badge');
                    if (badge) {
                        badge.className = 'badge status-badge ' + {
                            'pending': 'bg-warning text-dark',
                            'confirmed': 'bg-success',
                            'completed': 'bg-secondary',
                            'cancelled': 'bg-danger'
                        }[newStatus];
                        badge.textContent = {
                            'pending': 'Ожидает',
                            'confirmed': 'Подтверждено',
                            'completed': 'Завершено',
                            'cancelled': 'Отменено'
                        }[newStatus];
                    }
                    this.dataset.original = newStatus;
                }
            } catch (e) {
                alert('Ошибка сети');
                this.value = original;
            }
        });
    });
});