/**
 * Sistema de Validación en Tiempo Real
 * Proporciona feedback visual inmediato en formularios
 */

window.FieldValidator = {
    // Reglas de validación
    rules: {
        required: (value) => value && value.trim() !== '',
        email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
        phone: (value) => /^[\d\s\-\+\(\)]{7,}$/.test(value),
        minLength: (value, min) => value && value.length >= min,
        maxLength: (value, max) => value && value.length <= max,
        number: (value) => !isNaN(value) && value !== '',
        positive: (value) => parseFloat(value) > 0,
        date: (value) => {
            const date = new Date(value);
            return date instanceof Date && !isNaN(date);
        },
        futureDate: (value) => {
            const date = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return date >= today;
        }
    },

    // Mensajes de error
    messages: {
        required: 'Este campo es obligatorio',
        email: 'Ingresa un email válido',
        phone: 'Ingresa un teléfono válido',
        minLength: 'Debe tener al menos {min} caracteres',
        maxLength: 'Debe tener máximo {max} caracteres',
        number: 'Debe ser un número válido',
        positive: 'Debe ser un número mayor a 0',
        date: 'Ingresa una fecha válida',
        futureDate: 'La fecha debe ser hoy o posterior'
    },

    /**
     * Validar un campo individual
     */
    validateField(input, rules = []) {
        const value = input.value;
        const container = input.closest('.field-container') || input.parentElement;
        
        // Limpiar estado previo
        this.clearValidation(input);

        // Ejecutar validaciones
        for (const rule of rules) {
            let isValid = false;
            let message = '';
            
            if (typeof rule === 'string') {
                // Regla simple sin parámetros
                isValid = this.rules[rule](value);
                message = this.messages[rule];
            } else if (typeof rule === 'object') {
                // Regla con parámetros
                const ruleName = rule.name;
                isValid = this.rules[ruleName](value, ...rule.params);
                message = this.messages[ruleName].replace(/{(\w+)}/g, (_, key) => rule.params[key] || '');
            }

            if (!isValid) {
                this.markInvalid(input, message, container);
                return false;
            }
        }

        this.markValid(input, container);
        return true;
    },

    /**
     * Marcar campo como válido
     */
    markValid(input, container) {
        input.classList.remove('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
        input.classList.add('border-green-300', 'focus:ring-green-500', 'focus:border-green-500');
        
        // Agregar checkmark
        const existing = container.querySelector('.validation-icon');
        if (existing) existing.remove();

        const icon = document.createElement('div');
        icon.className = 'validation-icon absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none';
        icon.innerHTML = `
            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
        `;
        
        if (container.style.position !== 'absolute' && container.style.position !== 'relative') {
            container.style.position = 'relative';
        }
        container.appendChild(icon);
    },

    /**
     * Marcar campo como inválido
     */
    markInvalid(input, message, container) {
        input.classList.remove('border-green-300', 'focus:ring-green-500', 'focus:border-green-500');
        input.classList.add('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
        
        // Limpiar iconos previos
        const existing = container.querySelector('.validation-icon');
        if (existing) existing.remove();

        // Agregar ícono de error
        const icon = document.createElement('div');
        icon.className = 'validation-icon absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none';
        icon.innerHTML = `
            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
        `;
        
        if (container.style.position !== 'absolute' && container.style.position !== 'relative') {
            container.style.position = 'relative';
        }
        container.appendChild(icon);

        // Agregar mensaje de error
        const errorMsg = container.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.textContent = message;
        } else {
            const msg = document.createElement('p');
            msg.className = 'error-message text-red-500 text-xs mt-1';
            msg.textContent = message;
            container.appendChild(msg);
        }
    },

    /**
     * Limpiar validación
     */
    clearValidation(input) {
        const container = input.closest('.field-container') || input.parentElement;
        input.classList.remove(
            'border-red-300', 'focus:ring-red-500', 'focus:border-red-500',
            'border-green-300', 'focus:ring-green-500', 'focus:border-green-500'
        );
        
        const icon = container.querySelector('.validation-icon');
        if (icon) icon.remove();
        
        const errorMsg = container.querySelector('.error-message');
        if (errorMsg) errorMsg.remove();
    },

    /**
     * Inicializar validación automática en un formulario
     */
    initForm(formElement) {
        const inputs = formElement.querySelectorAll('[data-validate]');
        
        inputs.forEach(input => {
            const rulesAttr = input.getAttribute('data-validate');
            const rules = rulesAttr.split('|').map(rule => {
                if (rule.includes(':')) {
                    const [name, paramsStr] = rule.split(':');
                    const params = paramsStr.split(',').reduce((acc, param, idx) => {
                        const [key, value] = param.includes('=') ? param.split('=') : [idx, param];
                        acc[key] = value;
                        return acc;
                    }, {});
                    return { name, params };
                }
                return rule;
            });

            // Validar en blur (cuando pierde foco)
            input.addEventListener('blur', () => {
                if (input.value) {
                    this.validateField(input, rules);
                }
            });

            // Limpiar en focus si estaba inválido
            input.addEventListener('focus', () => {
                if (input.classList.contains('border-red-300')) {
                    this.clearValidation(input);
                }
            });

            // Revalidar en input si ya fue validado
            input.addEventListener('input', () => {
                if (input.classList.contains('border-red-300') || input.classList.contains('border-green-300')) {
                    this.validateField(input, rules);
                }
            });
        });
    },

    /**
     * Validar formulario completo
     */
    validateForm(formElement) {
        const inputs = formElement.querySelectorAll('[data-validate]');
        let isValid = true;

        inputs.forEach(input => {
            const rulesAttr = input.getAttribute('data-validate');
            const rules = rulesAttr.split('|').map(rule => {
                if (rule.includes(':')) {
                    const [name, paramsStr] = rule.split(':');
                    const params = paramsStr.split(',').reduce((acc, param, idx) => {
                        const [key, value] = param.includes('=') ? param.split('=') : [idx, param];
                        acc[key] = value;
                        return acc;
                    }, {});
                    return { name, params };
                }
                return rule;
            });

            if (!this.validateField(input, rules)) {
                isValid = false;
            }
        });

        return isValid;
    }
};

// Auto-inicializar formularios con clase .validate-form
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.validate-form').forEach(form => {
        FieldValidator.initForm(form);
    });
});
