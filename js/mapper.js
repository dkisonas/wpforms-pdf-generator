import {InvoiceData} from './classes.js';
import {PersonalData} from './classes.js';
import {ProductData} from './classes.js';

export function mapDataToInvoice(data) {
    const invoiceData = new InvoiceData();
    const personalData = new PersonalData();

    data.personalData.forEach(field => {
        mapPersonalData(personalData, field);
    });

    invoiceData.personalData = personalData;

    const products = data.products.map(productFields => {
        const product = new ProductData();
        productFields.forEach(field => {
            mapProductData(product, field);
        });
        product.totalPrice = product.basePrice * product.quantity;
        return product;
    });

    invoiceData.products = products;
    invoiceData.finalPrice = products.reduce((sum, product) => sum + product.totalPrice, 0.0);

    return invoiceData;
}

export function mapPersonalData(personalData, field) {
    const value = field.value;

    switch (field.label) {
        case 'Įmonės pavadinimas':
            personalData.companyName = value;
            break;
        case 'Įmonės kodas':
            personalData.companyCode = value;
            break;
        case 'PVM kodas':
            personalData.pvmCode = value;
            break;
        case 'Mobilusis':
            personalData.mobile = value;
            break;
        case 'Adresas':
            personalData.address = value;
            break;
        case 'El.Pašto adresas':
            personalData.email = value;
            break;
        case 'Vardas ir Pavardė':
            personalData.name = value;
            break;
    }
}

function mapProductData(product, field) {
    const value = field.value;

    switch (field.label) {
        case 'Pasirinkite kategoriją':
            product.category = value;
            break;
        case 'Pasirinkite stiklo paketo tipą':
            product.glassPackageType = value;
            break;
        case 'Ar reikia siaurinti stiklajuoste?':
            product.narrowGlazing = value;
            break;
        case 'Stiklo paketo aukštis mm.':
            product.height = value;
            break;
        case 'Stiklo paketo plotis mm.':
            product.width = value;
            break;
        case 'Pasirinkti stiklo paketo rėmelį':
            product.frame = value;
            break;
        case 'Transportavimas':
            product.transport = value;
            break;
        case 'Imitacijos stiklo pakete':
            product.glassImitation = value;
            break;
        case 'Seno stiklo paketo išvežimas':
            product.oldGlassRemoval = value;
            break;
        case 'Galutinė kaina':
            product.finalPrice = parseFloat(value.replace(/ /g, '').replace(',', '.'));
            break;
        case 'Pasirinkite dviejų stiklo paketo storį':
        case 'Pasirinkite trijų stiklo paketo storį':
            product.glassThickness = value;
            break;
        case 'Pasirinkite stiklo paketo struktūrą':
            product.glassStructure = value;
            break;
        case 'Pakeitimo darbai':
        case 'Pakeitimo darbai klijuotos medienos':
        case 'Pakeitimo darbai šarvo durys':
            product.replacementWork = value;
            break;
        case 'Kiekis':
            product.quantity = parseInt(value, 10);
            break;
        case 'Kaina':
            product.basePrice = parseFloat(value.replace(/ /g, '').replace(',', '.'));
            break;
    }
}