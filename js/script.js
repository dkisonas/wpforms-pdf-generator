document.addEventListener('DOMContentLoaded', () => {
    initializeButtonListeners();
});

function initializeButtonListeners() {
    document.addEventListener('click', handleButtonClick);
}

function handleButtonClick(event) {
    if (event.target && event.target.classList.contains('wpforms-page-next')) {
        setTimeout(addGeneratePDFButton, 100);
    } else if (event.target && event.target.id === 'button-generate-pdf') {
        event.preventDefault();
        const structuredData = collectStructuredFormData();
        console.log('Collected Structured Data:', structuredData);
        sendFormDataToServer(structuredData);
    }
}

function addGeneratePDFButton() {
    const submitContainers = document.getElementsByClassName('wpforms-submit-container');
    if (submitContainers.length > 0) {
        const submitContainer = submitContainers[0];
        if (!document.getElementById('button-generate-pdf')) {
            const customButton = createGeneratePDFButton();
            const submitButton = submitContainer.querySelector('.wpforms-submit');
            submitContainer.insertBefore(customButton, submitButton);
            console.log('Generate PDF button added');
        }
    }
}

function createGeneratePDFButton() {
    const button = document.createElement('button');
    button.type = 'submit';
    button.id = 'button-generate-pdf';
    button.className = 'wpforms-submit button-generate-pdf';
    button.innerHTML = 'Generate PDF';
    button.setAttribute('data-alt-text', 'Generating...');
    button.setAttribute('data-submit-text', 'Generate PDF');
    button.setAttribute('aria-live', 'assertive');
    return button;
}

function sendFormDataToServer(data) {
    fetch(customNumberToWords.generatePdfUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(response => response.blob())
        .then(blob => {
            console.log('Server response received, creating PDF download link');
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'generated_pdf.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => console.error('Error generating PDF:', error));
}

function collectStructuredFormData() {
    const form = document.getElementById('wpforms-form-1825');
    const formData = new FormData(form);
    const structuredData = [];
    const labelCounts = {};

    form.querySelectorAll('.wpforms-field').forEach(fieldContainer => {
        const fieldset = fieldContainer.querySelector('fieldset');
        if (fieldset) {
            collectFieldsetData(fieldset, formData, structuredData, labelCounts);
        } else {
            collectFieldData(fieldContainer, formData, structuredData, labelCounts);
        }
    });

    return structuredData.filter(item => item.value !== '');
}

function collectFieldsetData(fieldset, formData, structuredData, labelCounts) {
    const legend = fieldset.querySelector('legend')?.textContent.trim();
    const inputs = fieldset.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        processInput(input, legend, formData, structuredData, labelCounts);
    });
}

function collectFieldData(fieldContainer, formData, structuredData, labelCounts) {
    const label = fieldContainer.querySelector('label')?.textContent.trim();
    const inputs = fieldContainer.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        processInput(input, label, formData, structuredData, labelCounts);
    });
}

function processInput(input, label, formData, structuredData, labelCounts) {
    if (!labelCounts[label]) {
        labelCounts[label] = 0;
    }

    if (labelCounts[label] >= 1) {
        return;
    }

    if (input.type === 'radio' || input.type === 'checkbox') {
        if (input.checked) {
            const choiceLabel = input.closest('fieldset').querySelector(`label[for="${input.id}"]`)?.textContent.trim();
            structuredData.push({ label: label, value: choiceLabel || input.value });
            labelCounts[label]++;
        }
    } else if (input.tagName === 'SELECT') {
        const selectedOption = input.options[input.selectedIndex];
        const selectedOptionText = selectedOption ? selectedOption.textContent.trim() : '';
        const quantityInput = document.getElementById(`${input.id}-quantity`);
        if (quantityInput && quantityInput.value) {
            structuredData.push({ label: label, value: `${selectedOptionText} (Quantity: ${quantityInput.value})` });
        } else {
            structuredData.push({ label: label, value: selectedOptionText });
        }
        labelCounts[label]++;
    } else if (formData.has(input.name)) {
        structuredData.push({ label: label, value: formData.get(input.name) });
        labelCounts[label]++;
    }
}