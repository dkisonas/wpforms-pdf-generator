import { mapDataToInvoice } from './mapper.js';

let allowFormSubmit = false;
const FORM_ID = 'wpforms-form-1825';

document.addEventListener('DOMContentLoaded', () => {
    initializeButtonListeners();
    resetLocalStorage();
});

function initializeButtonListeners() {
    document.addEventListener('click', handleButtonClick);
}

function handleButtonClick(event) {
    const target = event.target;

    if (target && target.classList.contains('wpforms-page-next')) {
        savePersonalDataToLocalStorage();
        setTimeout(addGenerateProductButton, 100);
    } else if (target && target.id === 'button-add-product') {
        event.preventDefault();
        const productData = collectStructuredFormData('product');
        saveProductDataToLocalStorage(productData);
        clearForm();
    } else if (target && target.classList.contains('wpforms-submit') && !allowFormSubmit) {
        event.preventDefault();
        const form = getForm();
        const invalidElements = getInvalidElements(form);

        if (invalidElements.length > 0) {
            invalidElements[0].reportValidity();
            return;
        }
        const productData = collectStructuredFormData('product');
        saveProductDataToLocalStorage(productData);
        const personalData = JSON.parse(localStorage.getItem('personalData')) || [];
        const allData = {personalData, products: JSON.parse(localStorage.getItem('productData')) || []};
        const invoiceData = mapDataToInvoice(allData);
        sendFormDataToServer(invoiceData, event);
    }
}

function addGenerateProductButton() {
    const submitContainer = document.querySelector('.wpforms-submit-container');
    if (submitContainer && !document.getElementById('button-add-product')) {
        const customButton = createButton('button-add-product', 'Išsaugoti ir pridėti kitą produktą');
        const submitButton = submitContainer.querySelector('.wpforms-submit');
        submitContainer.insertBefore(customButton, submitButton);
    }
}

function createButton(id, text) {
    const button = document.createElement('button');
    button.type = 'submit';
    button.id = id;
    button.className = 'wpforms-submit';
    button.innerHTML = text;
    return button;
}

function resetLocalStorage() {
    localStorage.setItem('productData', JSON.stringify([]));
    localStorage.setItem('personalData', JSON.stringify([]));
}

function saveProductDataToLocalStorage(productData) {
    const storedData = JSON.parse(localStorage.getItem('productData')) || [];
    storedData.push(productData);
    localStorage.setItem('productData', JSON.stringify(storedData));
}

function savePersonalDataToLocalStorage() {
    const personalData = collectStructuredFormData('personal');
    localStorage.setItem('personalData', JSON.stringify(personalData));
}

function getForm() {
    return document.getElementById(FORM_ID);
}

function collectStructuredFormData(type) {
    const form = getForm();
    if (!form) {
        console.error('Form not found');
        return [];
    }

    const structuredData = [];

    form.querySelectorAll('.wpforms-field').forEach(fieldContainer => {
        const label = fieldContainer.querySelector('label, legend')?.textContent.trim().replace('*', '').trim();
        if (!label) return;

        const inputs = fieldContainer.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            let value = '';

            if (input.type === 'checkbox' || input.type === 'radio') {
                if (input.checked) value = input.nextElementSibling?.textContent.trim();
            } else if (input.tagName === 'SELECT') {
                const selectedOption = input.options[input.selectedIndex];
                value = selectedOption ? selectedOption.textContent.trim() : '';
            } else {
                value = input.value.trim();
            }

            if (value) {
                structuredData.push({ label, value });
            }
        });
    });

    return filterDataByType(structuredData, type);
}

function filterDataByType(data, type) {
    const personalFields = [
        'Įmonės pavadinimas', 'Įmonės kodas', 'PVM kodas', 'Mobilusis',
        'Adresas', 'El.Pašto adresas', 'Vardas ir Pavardė', 'Pašto', 'Vardas'
    ];

    const productFields = [
        'Pasirinkite kategoriją', 'Pasirinkite stiklo paketo tipą', 'Ar reikia siaurinti stiklajuoste?',
        'Stiklo paketo aukštis mm.', 'Stiklo paketo plotis mm.', 'Pasirinkti stiklo paketo rėmelį',
        'Transportavimas', 'Imitacijos stiklo pakete', 'Seno stiklo paketo išvežimas',
        'Galutinė kaina', 'Pasirinkite dviejų stiklo paketo storį', 'Pasirinkite trijų stiklo paketo storį',
        'Pasirinkite stiklo paketo struktūrą', 'Pakeitimo darbai', 'Pakeitimo darbai klijuotos medienos',
        'Pakeitimo darbai šarvo durys', 'Pasirinkti stiklo paketo rėmeli', 'Pasirinkite stiklo paketo rėmelį',
    ];

    const isFieldIncluded = (fieldList, label) => fieldList.some(field => label.includes(field));

    return data.filter(item => {
        if (type === 'personal') {
            return isFieldIncluded(personalFields, item.label);
        } else if (type === 'product') {
            return isFieldIncluded(productFields, item.label);
        }
        return false;
    });
}

function sendFormDataToServer(data, event) {
    fetch(window.customNumberToWords.generatePdfUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    }).then(_ => {
        allowFormSubmit = true;
        resetLocalStorage();
        event.target.click();
    })
}

function clearForm() {
    const form = getForm();
    if (form) {
        form.reset();
    }
}

function getInvalidElements(form) {
    const invalidElements = [];
    const fields = form.querySelectorAll('input, select, textarea');
    fields.forEach(field => {
        if (field.required && !field.disabled && field.offsetParent !== null && !field.checkValidity()) {
            invalidElements.push(field);
        }
    });
    return invalidElements;
}