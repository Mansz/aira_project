# Live Streaming Management - Admin Panel

## Overview
Live Streaming Management adalah fitur yang memungkinkan admin untuk melakukan live streaming dengan integrasi e-commerce, mengelola pesanan real-time, dan mengawasi komentar pengguna.

## Fitur Utama

### 1. Live Streaming (`LiveStreamingPage.tsx`)
- **Live Video Streaming**: Menggunakan ZEGOCLOUD untuk video streaming
- **Product Pinning**: Kemampuan untuk pin/unpin produk selama streaming
- **Real-time Analytics**: Tracking viewer count, comments, dan engagement
- **Stream Controls**: Start/stop streaming dengan kontrol audio/video

**Fitur Utama:**
- Environment variables untuk konfigurasi ZEGOCLOUD
- Token-based authentication untuk streaming
- Real-time viewer count
- Camera dan microphone controls
- Stream title configuration
- Error handling dan loading states

### 2. Live Orders Management (`LiveOrdersPage.tsx`)
- **Real-time Order Tracking**: Monitor pesanan yang masuk selama live streaming
- **Order Confirmation**: Konfirmasi pesanan secara real-time
- **Revenue Analytics**: Dashboard dengan statistik pendapatan
- **Customer Information**: Detail lengkap pelanggan dan produk

**Fitur Utama:**
- Real-time order notifications via WebSocket
- Order status management (pending, confirmed, cancelled)
- Payment status tracking
- Revenue calculation dan analytics
- Search dan filter functionality
- Order detail dialog dengan informasi lengkap

### 3. Live Comments Management (`LiveCommentsPage.tsx`)
- **Comment Moderation**: Hapus komentar yang tidak sesuai
- **Real-time Monitoring**: Monitor semua komentar secara real-time
- **Comment Classification**: Pembedaan antara komentar biasa dan order
- **Flagged Comments**: Identifikasi komentar bermasalah

**Fitur Utama:**
- Real-time comment updates via WebSocket
- Comment deletion dengan confirmation dialog
- Comment type classification (CHAT/ORDER)
- Flagged comment detection
- Search functionality
- User information display

## API Integration

### Streaming APIs
```typescript
// Get stream token
api.getStreamToken(streamTitle: string)

// Start live stream
api.startLiveStream(data: { title: string; description?: string; stream_id?: string })

// End live stream
api.endLiveStream()

// Pin/Unpin products
api.pinProductToStream(streamId: string, productId: number)
api.unpinProductFromStream(streamId: string)

// Save analytics
api.saveStreamAnalytics(streamId: string, data: { total_comments: number; active_users: number })
```

### Orders APIs
```typescript
// Get live orders
api.getLiveOrders()

// Confirm order
api.confirmLiveOrder(orderId: string)

// Update order status
api.updateLiveOrderStatus(orderId: string, status: string)
```

### Comments APIs
```typescript
// Get live comments
api.getLiveComments(streamId?: string)

// Delete comment
api.deleteLiveComment(commentId: number)
```

## Real-time Features (WebSocket)

### Order Events
- `NewOrder`: Pesanan baru masuk
- `OrderUpdated`: Update status pesanan
- `OrderConfirmed`: Pesanan dikonfirmasi

### Comment Events
- `NewComment`: Komentar baru
- `CommentDeleted`: Komentar dihapus

### Stream Events
- `ViewerCountUpdated`: Update jumlah viewer
- `ProductPinned`: Produk di-pin ke stream

## Environment Variables

Untuk menggunakan fitur streaming, tambahkan konfigurasi berikut di `.env`:

```env
# ZEGOCLOUD Configuration
REACT_APP_ZEGO_APP_ID=your_zego_app_id
REACT_APP_ZEGO_SERVER_URL=wss://webliveroom-test.zego.im/ws

# Pusher Configuration (untuk WebSocket)
REACT_APP_PUSHER_APP_KEY=your_pusher_key
REACT_APP_PUSHER_APP_CLUSTER=your_pusher_cluster
```

## Dependencies

### Required Packages
```json
{
  "zego-express-engine-webrtc": "^3.0.0",
  "laravel-echo": "^1.15.0",
  "pusher-js": "^8.3.0"
}
```

### Install Dependencies
```bash
npm install zego-express-engine-webrtc laravel-echo pusher-js
```

## Type Definitions

Semua type definitions tersedia di `src/types/stream.ts`:

- `LiveStream`: Data stream utama
- `LiveOrder`: Data pesanan live
- `LiveComment`: Data komentar live
- `LiveOrderProduct`: Produk dalam pesanan
- `StreamStats`: Statistik streaming
- Event types untuk WebSocket

## Implementation Status

✅ **Completed:**
- Live streaming interface dengan ZEGOCLOUD
- Real-time order management
- Real-time comment management
- WebSocket integration
- Type definitions
- API integration
- Error handling dan loading states

⏳ **Pending:**
- Backend API implementation
- WebSocket server setup (Laravel Pusher)
- Database migrations untuk streaming tables
- ZEGOCLOUD account setup
- Pusher account setup

## Usage Examples

### Starting a Live Stream
```typescript
const startLiveStream = async () => {
  try {
    // Get token from backend
    const tokenResponse = await api.getStreamToken(streamTitle);
    
    // Initialize ZEGOCLOUD
    await zg.loginRoom(streamTitle, tokenResponse.data.token, userInfo);
    
    // Create and publish stream
    const stream = await zg.createStream({ camera: { audio: true, video: true } });
    await zg.startPublishingStream(streamTitle, stream);
    
    // Save stream info
    await api.startLiveStream({ title: streamTitle, stream_id: stream.streamID });
  } catch (error) {
    console.error('Failed to start stream:', error);
  }
};
```

### Confirming Live Order
```typescript
const confirmOrder = async (orderId: string) => {
  try {
    await api.confirmLiveOrder(orderId);
    // Update akan diterima via WebSocket
  } catch (error) {
    console.error('Failed to confirm order:', error);
  }
};
```

## Next Steps

1. **Backend Implementation**: Implementasi API endpoints di Laravel
2. **Database Setup**: Buat migrations untuk live streaming tables
3. **WebSocket Setup**: Konfigurasi Laravel Pusher untuk real-time events
4. **External Services**: Setup ZEGOCLOUD dan Pusher accounts
5. **Testing**: Integration testing dengan real streaming scenario
6. **Mobile App Integration**: Integrasi dengan aplikasi mobile customer

## File Structure

```
src/pages/streaming/
├── LiveStreamingPage.tsx     # Main streaming interface
├── LiveOrdersPage.tsx        # Order management
├── LiveCommentsPage.tsx      # Comment moderation
└── README.md                 # This documentation

src/lib/
├── api.ts                    # Updated with streaming APIs
└── echo.ts                   # WebSocket configuration

src/types/
└── stream.ts                 # Type definitions
