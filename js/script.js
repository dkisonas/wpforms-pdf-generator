let allowFormSubmit = false;
const FORM_ID = 'wpforms-form-13'

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM fully loaded and parsed');
    initializeButtonListeners();
    resetLocalStorage();
    console.log('Button listeners initialized and local storage reset');
});

function initializeButtonListeners() {
    console.log('Initializing button listeners');
    document.addEventListener('click', handleButtonClick);
}

function handleButtonClick(event) {
    const target = event.target;

    if (target && target.classList.contains('wpforms-page-next')) {
        setTimeout(addGenerateProductButton, 100);
    } else if (target && target.id === 'button-add-product') {
        event.preventDefault();
        console.log('Add Product button clicked');
        const productData = collectStructuredFormData('product');
        console.log('Collected Product Data:', productData);
        saveProductDataToLocalStorage(productData);
        clearForm();
    } else if (target && target.classList.contains('wpforms-submit') && !allowFormSubmit) {
        event.preventDefault();
        console.log('Submit button clicked');
        const productData = collectStructuredFormData('product');
        console.log('Collected Product Data on Submit:', productData);
        saveProductDataToLocalStorage(productData);
        const personalData = collectStructuredFormData('personal');
        console.log('Collected Personal Data:', personalData);
        const allData = { personalData, products: JSON.parse(localStorage.getItem('productData')) || [] };
        console.log('All Data to be Sent:', allData);
        // sendFormDataToServer(allData, event);
    }
}

function addGenerateProductButton() {
    const submitContainer = document.querySelector('.wpforms-submit-container');
    if (submitContainer && !document.getElementById('button-add-product')) {
        const customButton = createButton('button-add-product', 'Išsaugoti ir pridėti kitą produktą');
        const submitButton = submitContainer.querySelector('.wpforms-submit');
        submitContainer.insertBefore(customButton, submitButton);
        console.log('Generate Product button added');
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
    console.log('Local storage reset');
}

function saveProductDataToLocalStorage(productData) {
    const storedData = JSON.parse(localStorage.getItem('productData')) || [];
    storedData.push(productData);
    localStorage.setItem('productData', JSON.stringify(storedData));
    console.log('Product data saved to local storage:', storedData);
}

function collectStructuredFormData(type) {
    const form = document.getElementById(FORM_ID);
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
        'Pakeitimo darbai šarvo durys'
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
    console.log('Sending form data to server:', data);
    fetch(customNumberToWords.generatePdfUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'generated_pdf.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            allowFormSubmit = true;
            resetLocalStorage();
            event.target.click();
            console.log('PDF generated and downloaded');
        })
        .catch(error => console.error('Error generating PDF:', error));
}

function clearForm() {
    const form = document.getElementById(FORM_ID);
    if (form) {
        form.reset();
        console.log('Form cleared');
    } else {
        console.error('Form not found');
    }
}