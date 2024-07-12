export class PersonalData {
    constructor() {
        this.companyName = '';
        this.companyCode = '';
        this.pvmCode = '';
        this.mobile = '';
        this.address = '';
        this.email = '';
        this.name = '';
    }
}

export class ProductData {
    constructor() {
        this.category = '';
        this.glassPackageType = '';
        this.isNarrowGlazingNeeded = false;
        this.height = '';
        this.width = '';
        this.frameType = '';
        this.isTransportNeeded = false;
        this.hasGlassImitation = false;
        this.isOldGlassRemovalNeeded = false;
        this.glassThickness = '';
        this.glassStructure = '';
        this.isReplacementWorkNeeded = false;
        this.basePrice = 0.0;
        this.quantity = 0;
        this.totalPrice = 0.0;
    }
}

export class InvoiceData {
    constructor() {
        this.personalData = new PersonalData();
        this.finalPrice = 0.0;
        this.products = [];
    }
}