document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded and parsed');
    initializeButtonListeners();
});

function initializeButtonListeners() {
    console.log('Initializing button listeners');
    document.addEventListener('click', handleButtonClick);
}

function handleButtonClick(event) {
    console.log('Button clicked:', event.target);
    if (event.target && event.target.classList.contains('wpforms-page-next')) {
        console.log('Next page button clicked');
        setTimeout(addGeneratePDFButton, 100);
    } else if (event.target && event.target.id === 'button-generate-pdf') {
        event.preventDefault();
        console.log('Generate PDF button clicked');
        const structuredData = collectStructuredFormData();
        console.log('Collected Structured Data:', structuredData);
        sendFormDataToServer(structuredData);
    }
}

function addGeneratePDFButton() {
    console.log('Attempting to add Generate PDF button');
    const submitContainers = document.getElementsByClassName('wpforms-submit-container');
    if (submitContainers.length > 0) {
        const submitContainer = submitContainers[0];
        if (!document.getElementById('button-generate-pdf')) {
            const customButton = createGeneratePDFButton();
            const submitButton = submitContainer.querySelector('.wpforms-submit');
            submitContainer.insertBefore(customButton, submitButton);
            console.log('Generate PDF button added');
        }
    } else {
        console.log('No submit container found');
    }
}

function createGeneratePDFButton() {
    console.log('Creating Generate PDF button');
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
    console.log('Sending form data to server:', data);
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
    if (!form) {
        console.error('Form not found');
        return [];
    }
    console.log('Collecting structured form data');
    const formData = new FormData(form);
    const structuredData = [];
    const labelCounts = {};

    form.querySelectorAll('.wpforms-field').forEach(fieldContainer => {
        const fieldset = fieldContainer.querySelector('fieldset');
        if (fieldset) {
            console.log('Processing fieldset:', fieldset);
            collectFieldsetData(fieldset, formData, structuredData, labelCounts);
        } else {
            console.log('Processing field container:', fieldContainer);
            collectFieldData(fieldContainer, formData, structuredData, labelCounts);
        }
    });

    const filteredData = structuredData.filter(item => item.value !== '');
    console.log('Filtered Structured Data:', filteredData);
    return filteredData;
}

function collectFieldsetData(fieldset, formData, structuredData, labelCounts) {
    const legend = fieldset.querySelector('legend')?.textContent.trim();
    console.log('Collecting data for fieldset with legend:', legend);
    const inputs = fieldset.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        console.log('Processing input in fieldset:', input);
        processInput(input, legend, formData, structuredData, labelCounts);
    });
}

function collectFieldData(fieldContainer, formData, structuredData, labelCounts) {
    const label = fieldContainer.querySelector('label')?.textContent.trim();
    console.log('Collecting data for field container with label:', label);
    const inputs = fieldContainer.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        console.log('Processing input in field container:', input);
        processInput(input, label, formData, structuredData, labelCounts);
    });
}

function processInput(input, label, formData, structuredData, labelCounts) {
    console.log('Processing input:', input, 'with label:', label);
    if (!labelCounts[label]) {
        labelCounts[label] = 0;
    }

    if (labelCounts[label] >= 1) {
        console.log('Skipping input due to duplicate label:', label);
        return;
    }

    if (input.type === 'radio' || input.type === 'checkbox') {
        if (input.checked) {
            const choiceLabel = input.closest('fieldset').querySelector(`label[for="${input.id}"]`)?.textContent.trim();
            structuredData.push({ label: label, value: choiceLabel || input.value });
            labelCounts[label]++;
            console.log('Added radio/checkbox input:', choiceLabel || input.value);
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
        console.log('Added select input:', selectedOptionText);
    } else if (formData.has(input.name)) {
        structuredData.push({ label: label, value: formData.get(input.name) });
        labelCounts[label]++;
        console.log('Added input:', formData.get(input.name));
    }
}