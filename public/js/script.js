/**
 * Universe Zero - Scripts Principales
 * Validaciones, interacciones y mejoras de UX
 */

document.addEventListener('DOMContentLoaded', function() {

    // ==============================
    // 1. VALIDACIÓN DE FORMULARIOS
    // ==============================

    // Validar campos requeridos en formularios con atributo data-validate
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const required = this.querySelectorAll('[required]');
            let valid = true;
            let firstInvalid = null;

            required.forEach(field => {
                // Limpiar estado previo
                field.style.borderColor = '';
                field.classList.remove('is-invalid');

                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    field.classList.add('is-invalid');
                    valid = false;
                    if (!firstInvalid) firstInvalid = field;
                } else {
                    // Validaciones específicas por tipo
                    if (field.type === 'email' && !isValidEmail(field.value)) {
                        field.style.borderColor = '#dc3545';
                        field.classList.add('is-invalid');
                        valid = false;
                        if (!firstInvalid) firstInvalid = field;
                    }
                    if (field.type === 'password' && field.id === 'password' && field.value.length < 8) {
                        field.style.borderColor = '#dc3545';
                        field.classList.add('is-invalid');
                        valid = false;
                        if (!firstInvalid) firstInvalid = field;
                    }
                }
            });

            // Mostrar mensaje de error general
            if (!valid) {
                e.preventDefault();
                const errorMsg = document.getElementById('form-error') || createErrorAlert();
                errorMsg.textContent = 'Por favor, complete todos los campos obligatorios correctamente.';
                errorMsg.style.display = 'block';
                if (firstInvalid) {
                    firstInvalid.focus();
                }
            }
        });
    });

    // Validar coincidencia de contraseñas en registro
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    if (password && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.setCustomValidity('Las contraseñas no coinciden');
                this.style.borderColor = '#dc3545';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '';
            }
        });

        password.addEventListener('input', function() {
            if (this.value.length < 8) {
                this.setCustomValidity('La contraseña debe tener al menos 8 caracteres');
                this.style.borderColor = '#dc3545';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '';
            }
        });
    }

    // ==============================
    // 2. CONFIRMACIONES DE ELIMINACIÓN
    // ==============================

    const deleteButtons = document.querySelectorAll('.btn-delete, .btn-danger[data-confirm]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de eliminar este registro? Esta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        });
    });

    // Confirmación para vaciar carrito
    const clearCartBtn = document.querySelector('form .btn-danger[value="Vaciar Carrito"]');
    if (clearCartBtn) {
        clearCartBtn.closest('form').addEventListener('submit', function(e) {
            if (!confirm('¿Está seguro de vaciar todo el carrito?')) {
                e.preventDefault();
            }
        });
    }

    // ==============================
    // 3. FILTRADO EN TABLAS (búsqueda en vivo)
    // ==============================

    const searchInputs = document.querySelectorAll('.search-input, input[placeholder*="Buscar"]');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase().trim();
            const table = this.closest('.table-container')?.querySelector('table') ||
                          document.querySelector('.table-container table');
            if (!table) return;

            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    });

    // ==============================
    // 4. ACTUALIZACIÓN AUTOMÁTICA DEL CARRITO
    // ==============================

    // Para los formularios de actualización de cantidad en el carrito
    document.querySelectorAll('.update-cart-form input[name="quantity"]').forEach(input => {
        input.addEventListener('change', function() {
            this.closest('.update-cart-form').submit();
        });
    });

    // ==============================
    // 5. MANEJO DE MENSAJES FLASH (desvanecimiento)
    // ==============================

    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        // Desvanecer después de 5 segundos
        setTimeout(() => {
            alert.style.transition = 'opacity 1s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 1000);
        }, 5000);
    });

    // ==============================
    // 6. FUNCIONES AUXILIARES
    // ==============================

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function createErrorAlert() {
        const div = document.createElement('div');
        div.id = 'form-error';
        div.className = 'alert alert-error';
        div.style.display = 'none';
        const container = document.querySelector('.main-content .container');
        if (container) {
            container.insertBefore(div, container.firstChild);
        }
        return div;
    }

    // ==============================
    // 7. FUNCIONES PARA REPORTES (descarga simulada)
    // ==============================

    window.downloadReport = function(format) {
        alert('Descargando reporte en formato ' + format.toUpperCase() + '...\n(Simulación)');
        // Aquí se podría implementar una llamada AJAX para generar el reporte
        // Ejemplo: window.location.href = 'index.php?controller=report&action=download&format=' + format;
    };

    // ==============================
    // 8. PREPARACIÓN PARA AJAX (Agregar al carrito sin recargar)
    // ==============================

    // Nota: Esta funcionalidad está comentada porque el backend actual
    // espera un POST tradicional. Se puede descomentar y adaptar.

    /*
    document.querySelectorAll('.add-to-cart form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('index.php?controller=sale&action=addAjax', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar contador del carrito
                    const badge = document.querySelector('.cart-count');
                    if (badge) badge.textContent = data.cart_count;
                    showToast('Producto agregado al carrito');
                } else {
                    showToast(data.message || 'Error al agregar', 'error');
                }
            })
            .catch(() => showToast('Error de conexión', 'error'));
        });
    });

    function showToast(message, type = 'success') {
        // Implementar un toast simple
    }
    */

    console.log('Universe Zero - Scripts cargados correctamente.');
});