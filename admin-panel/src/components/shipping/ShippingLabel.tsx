import React from 'react';
import {
  Box,
  Typography,
  Grid,
  Divider,
  Paper,
} from '@mui/material';
import { Shipment } from '@/types/shipment';

interface ShippingLabelProps {
  shipment: Shipment;
}

export const ShippingLabel: React.FC<ShippingLabelProps> = ({ shipment }) => {
  return (
    <Paper
      sx={{
        p: 3,
        width: '400px',
        height: '600px',
        border: '2px solid #000',
        fontFamily: 'monospace',
        backgroundColor: 'white',
        color: 'black',
        '@media print': {
          border: '2px solid #000',
          boxShadow: 'none',
          margin: 0,
          padding: '20px',
        },
      }}
    >
      {/* Header */}
      <Box sx={{ textAlign: 'center', mb: 2 }}>
        <Typography variant="h6" sx={{ fontWeight: 'bold', fontSize: '18px' }}>
          SHIPPING LABEL
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '12px' }}>
          {shipment.courier.name} - {shipment.courier.service}
        </Typography>
      </Box>

      <Divider sx={{ mb: 2, borderColor: '#000' }} />

      {/* Tracking Number */}
      <Box sx={{ textAlign: 'center', mb: 3 }}>
        <Typography variant="h4" sx={{ fontWeight: 'bold', letterSpacing: 2 }}>
          {shipment.courier.trackingNumber}
        </Typography>
      </Box>

      <Divider sx={{ mb: 2, borderColor: '#000' }} />

      {/* Sender Information */}
      <Box sx={{ mb: 3 }}>
        <Typography variant="subtitle2" sx={{ fontWeight: 'bold', mb: 1 }}>
          FROM:
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '12px' }}>
          Your Store Name
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '12px' }}>
          Jl. Contoh No. 123
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '12px' }}>
          Jakarta Pusat, DKI Jakarta 10110
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '12px' }}>
          Phone: +62 21 1234567
        </Typography>
      </Box>

      <Divider sx={{ mb: 2, borderColor: '#000' }} />

      {/* Recipient Information */}
      <Box sx={{ mb: 3 }}>
        <Typography variant="subtitle2" sx={{ fontWeight: 'bold', mb: 1 }}>
          TO:
        </Typography>
        <Typography variant="body1" sx={{ fontWeight: 'bold', fontSize: '14px' }}>
          {shipment.customer.name}
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '12px' }}>
          {shipment.customer.phone}
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '12px', mt: 1 }}>
          {shipment.address.street}
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '12px' }}>
          {shipment.address.city}, {shipment.address.province}
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '12px' }}>
          {shipment.address.postalCode}
        </Typography>
      </Box>

      <Divider sx={{ mb: 2, borderColor: '#000' }} />

      {/* Package Information */}
      <Grid container spacing={2} sx={{ mb: 2 }}>
        <Grid item xs={6}>
          <Typography variant="body2" sx={{ fontSize: '10px' }}>
            <strong>Order ID:</strong> {shipment.orderId}
          </Typography>
        </Grid>
        <Grid item xs={6}>
          <Typography variant="body2" sx={{ fontSize: '10px' }}>
            <strong>Shipment ID:</strong> {shipment.id}
          </Typography>
        </Grid>
        <Grid item xs={6}>
          <Typography variant="body2" sx={{ fontSize: '10px' }}>
            <strong>Weight:</strong> {shipment.weight || '1'} kg
          </Typography>
        </Grid>
        <Grid item xs={6}>
          <Typography variant="body2" sx={{ fontSize: '10px' }}>
            <strong>Items:</strong> {shipment.items.length}
          </Typography>
        </Grid>
      </Grid>

      <Divider sx={{ mb: 2, borderColor: '#000' }} />

      {/* Items List */}
      <Box sx={{ mb: 2 }}>
        <Typography variant="subtitle2" sx={{ fontWeight: 'bold', mb: 1, fontSize: '12px' }}>
          ITEMS:
        </Typography>
        {shipment.items.slice(0, 3).map((item, index) => (
          <Typography key={index} variant="body2" sx={{ fontSize: '10px' }}>
            • {item.name} (x{item.quantity})
          </Typography>
        ))}
        {shipment.items.length > 3 && (
          <Typography variant="body2" sx={{ fontSize: '10px' }}>
            ... and {shipment.items.length - 3} more items
          </Typography>
        )}
      </Box>

      {/* Footer */}
      <Box sx={{ position: 'absolute', bottom: 20, left: 20, right: 20 }}>
        <Divider sx={{ mb: 1, borderColor: '#000' }} />
        <Typography variant="body2" sx={{ fontSize: '10px', textAlign: 'center' }}>
          Generated on {new Date().toLocaleDateString('id-ID')} at {new Date().toLocaleTimeString('id-ID')}
        </Typography>
        <Typography variant="body2" sx={{ fontSize: '8px', textAlign: 'center', mt: 1 }}>
          Handle with care • Fragile items inside
        </Typography>
      </Box>
    </Paper>
  );
};
