export interface Shipment {
  id: string;
  orderId: string;
  customer: {
    name: string;
    phone: string;
  };
  address: {
    street: string;
    city: string;
    province: string;
    postalCode: string;
  };
  courier: {
    name: string;
    service: string;
    trackingNumber: string;
  };
  status: 'processing' | 'in_transit' | 'out_for_delivery' | 'delivered' | string;
  createdAt: string;
  updatedAt: string;
  items: {
    name: string;
    quantity: number;
  }[];
  weight?: number;
}

export interface ShipmentStats {
  totalShipments: number;
  processing: number;
  inTransit: number;
  outForDelivery: number;
  delivered: number;
}
