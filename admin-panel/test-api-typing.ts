// Test file untuk memverifikasi typing API live streaming
import { api } from './src/lib/api';

async function testLiveStreamingTyping() {
  // Test getLiveStreamHistory - should return Promise<{ data: LiveStream[] }>
  const historyResponse = await api.getLiveStreamHistory();
  console.log('History response data type:', typeof historyResponse.data);
  console.log('First stream title:', historyResponse.data[0]?.title);

  // Test getActiveStream - should return Promise<{ data: LiveStream | null }>
  const activeResponse = await api.getActiveStream();
  console.log('Active stream data:', activeResponse.data?.title || 'No active stream');

  // Test getLiveStreamStats - should return Promise<{ data: StreamStats }>
  const statsResponse = await api.getLiveStreamStats();
  console.log('Stats response:', statsResponse.data);

  // Test startLiveStream - should return Promise<{ data: LiveStream }>
  const newStreamResponse = await api.startLiveStream({
    title: 'Test Stream',
    description: 'Test Description'
  });
  console.log('New stream created:', newStreamResponse.data.title);

  // Test getLiveOrders - should return Promise<{ data: LiveOrder[] }>
  const ordersResponse = await api.getLiveOrders();
  console.log('Live orders count:', ordersResponse.data.length);

  // Test getLiveComments - should return Promise<{ data: LiveComment[] }>
  const commentsResponse = await api.getLiveComments();
  console.log('Live comments count:', commentsResponse.data.length);

  // Test deleteLiveComment - should return Promise<{ message: string }>
  const deleteResponse = await api.deleteLiveComment(1);
  console.log('Delete comment response:', deleteResponse.message);
}

// TypeScript akan memberikan error jika typing tidak sesuai
export { testLiveStreamingTyping };
