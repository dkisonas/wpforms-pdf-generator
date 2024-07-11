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
        product.basePrice = product.totalPrice / product.quantity
        return product;
    });

    invoiceData.products = products;
    invoiceData.finalPrice = products.reduce((sum, product) => sum + product.totalPrice, 0.0);

    return invoiceData;
}

export function mapPersonalData(personalData, field) {
    const value = field.value || null;

    switch (field.label.trim().toLowerCase()) {
        case 'įmonės pavadinimas':
            personalData.companyName = value;
            break;
        case 'įmonės kodas':
            personalData.companyCode = value;
            break;
        case 'pvm kodas':
            personalData.pvmCode = value;
            break;
        case 'mobilusis':
            personalData.mobile = value;
            break;
        case 'adresas':
            personalData.address = value;
            break;
        case 'el.pašto adresas':
        case 'el. pašto adresas': // Added case for minor variations
            personalData.email = value;
            break;
        case 'vardas ir pavardė':
            personalData.name = value;
            break;
    }
}

function mapProductData(product, field) {
    let value = field.value || null;

    console.log(field)
    if (!value) return

    // Convert 'Reikia' and 'Nereikia' to true and false
    if (value === 'Reikia') value = true;
    if (value === 'Nereikia') value = false;

    const fieldLabel = field.label.trim().toLowerCase();

    switch (true) {
        case fieldLabel.includes('pasirinkite kategoriją'):
            product.category = value;
            break;
        case fieldLabel.includes('pasirinkite stiklo paketo tipą'):
            product.glassPackageType = value;
            break;
        case fieldLabel.includes('ar reikia siaurinti stiklajuoste'):
            product.isNarrowGlazingNeeded = value;
            break;
        case fieldLabel.includes('stiklo paketo aukštis mm'):
            product.height = value;
            break;
        case fieldLabel.includes('stiklo paketo plotis mm'):
            product.width = value;
            break;
        case fieldLabel.includes('pasirinkti stiklo paketo rėmelį'):
        case fieldLabel.includes('pasirinkti stiklo paketo rėmeli'):
        case fieldLabel.includes('pasirinkite stiklo paketo rėmelį'):
            product.frameType = value;
            break;
        case fieldLabel.includes('transportavimas'):
            product.isTransportNeeded = value;
            break;
        case fieldLabel.includes('imitacijos stiklo pakete'):
            product.hasGlassImitation = value;
            break;
        case fieldLabel.includes('seno stiklo paketo išvežimas'):
            product.hasOldGlassRemoval = value;
            break;
        case fieldLabel.includes('dviejų stiklo paketo storį'):
        case fieldLabel.includes('trijų stiklo paketo storį'):
            product.glassThickness = value;
            break;
        case fieldLabel.includes('pasirinkite stiklo paketo struktūrą'):
            if (product.glassStructure) {
                product.quantity = parseInt(value, 10);
            } else {
                product.glassStructure = value;
            }
            break;
        case fieldLabel.includes('pakeitimo darbai'):
        case fieldLabel.includes('pakeitimo darbai klijuotos medienos'):
        case fieldLabel.includes('pakeitimo darbai šarvo durys'):
            product.isReplacementWorkNeeded = value;
            break;
        case fieldLabel.includes('galutinė kaina'):
            const price = parseFloat(value.replace(/[^0-9,.-]+/g, "").replace(',', '.'));
            product.totalPrice = price;
            break;
        default:
            console.log('label not in switch: ' + field.label)
            break;
    }

}