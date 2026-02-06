document.addEventListener('alpine:init', () => {
    Alpine.data('acfConditionalLogic', (rules) => ({
        show: true,
        init() {
            this.evaluate();
            // Listen for changes on other fields
            window.addEventListener('acf-field-changed', () => this.evaluate());
        },
        evaluate() {
            if (!rules || rules.length === 0) return;
            
            let result = true;
            rules.forEach(rule => {
                const targetField = document.querySelector(`[name="acf[${rule.field}]"]`);
                if (!targetField) return;

                const value = targetField.type === 'checkbox' ? targetField.checked : targetField.value;
                
                switch (rule.operator) {
                    case '==': result = result && (value == rule.value); break;
                    case '!=': result = result && (value != rule.value); break;
                    // Add more operators as needed
                }
            });
            
            this.show = result;
        }
    }));
});

// Trigger change event
document.addEventListener('change', (e) => {
    if (e.target.name && e.target.name.startsWith('acf[')) {
        window.dispatchEvent(new CustomEvent('acf-field-changed'));
    }
});
