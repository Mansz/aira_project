// src/api.ts
import axios, { AxiosInstance, AxiosError } from 'axios';
import { OrderComplaint, OrderComplaintStats } from '../types/complaint';
import { Order, OrderStats } from '../types/order';
import { Payment } from '../types/payment';
import { Product, ProductCategory } from '../types/product';
import { Shipment, ShipmentStats } from '../types/shipment';
import { LiveStream } from '../types/stream';
import { Admin, AdminActivity } from '../types/admin';

// Use relative URLs when running in development with proxy
const BASE_URL = '';  // Remove /api since it's already included in the route

interface ApiResponse {
  message?: string;
  errors?: Record<string, string[]>;
  data?: any;
}

export class ApiError extends Error {
  constructor(
    message: string,
    public status?: number,
    public errors?: Record<string, string[]>
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

class Api {
  private client: AxiosInstance;

  constructor() {
    this.client = axios.create({
      baseURL: BASE_URL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    });

    this.client.interceptors.request.use((config) => {
      const token = localStorage.getItem('token');
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    });

    this.client.interceptors.response.use(
      (response) => response,
      (error: AxiosError) => {
        if (error.response?.status === 401) {
          localStorage.removeItem('token');
          window.location.href = '/login';
        }
        
        const message = (error.response?.data as ApiResponse)?.message || 'An error occurred';
        const status = error.response?.status;
        const errors = (error.response?.data as ApiResponse)?.errors;
        
        throw new ApiError(message, status, errors);
      }
    );
  }

  // --- AUTH ---
  async login(email: string, password: string) {
    try {
      await this.client.get('/sanctum/csrf-cookie');
    } catch (error) {
      console.warn('Failed to get CSRF cookie:', error);
    }
    
    const response = await this.client.post('/api/v1/auth/admin/login', {
      email,
      password,
    });
    
    if (response.data.data?.token) {
      localStorage.setItem('token', response.data.data.token);
    }
    
    return response.data;
  }

  async logout() {
    await this.client.post('/api/v1/auth/admin/logout');
  }

  async getProfile() {
    const response = await this.client.get('/api/v1/auth/admin/profile');
    return response.data;
  }

  // --- DASHBOARD ---
  async getDashboardStats() {
    const response = await this.client.get('/api/v1/admin/dashboard/stats');
    return response.data;
  }

  // --- PRODUCTS ---
  async getProducts(params?: Record<string, any>): Promise<{ data: Product[]; message: string }> {
    const response = await this.client.get('/api/v1/admin/products', { params });
    return {
      data: response.data.data,
      message: response.data.message
    };
  }

  async getProduct(id: number): Promise<{ data: Product; message: string }> {
    const response = await this.client.get(`/api/v1/admin/products/${id}`);
    return {
      data: response.data.data,
      message: response.data.message
    };
  }

  async createProduct(data: FormData): Promise<{ data: Product; message: string }> {
    const response = await this.client.post('/api/v1/admin/products', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return {
      data: response.data.data,
      message: response.data.message
    };
  }

  async updateProduct(id: number, data: FormData): Promise<{ data: Product; message: string }> {
    data.append('_method', 'PUT');
    
    const response = await this.client.post(`/api/v1/admin/products/${id}`, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return {
      data: response.data.data,
      message: response.data.message
    };
  }

  async deleteProduct(id: number): Promise<{ message: string }> {
    const response = await this.client.delete(`/api/v1/admin/products/${id}`);
    return {
      message: response.data.message
    };
  }

  // --- PRODUCT CATEGORIES ---
  async getCategories(params?: Record<string, any>): Promise<{ data: ProductCategory[] }> {
    const response = await this.client.get('/api/v1/admin/categories', { params });
    return { data: response.data.data };
  }

  async getCategory(id: number): Promise<{ data: ProductCategory }> {
    const response = await this.client.get(`/api/v1/admin/categories/${id}`);
    return { data: response.data.data };
  }

  async createCategory(data: FormData): Promise<{ data: ProductCategory }> {
    const response = await this.client.post('/api/v1/admin/categories', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return { data: response.data.data };
  }

  async updateCategory(id: number, data: FormData): Promise<{ data: ProductCategory }> {
    const response = await this.client.post(`/api/v1/admin/categories/${id}`, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'X-HTTP-Method-Override': 'PUT',
      },
    });
    return { data: response.data.data };
  }

  async deleteCategory(id: number): Promise<void> {
    await this.client.delete(`/api/v1/admin/categories/${id}`);
  }

  async toggleCategoryStatus(id: number): Promise<{ data: ProductCategory }> {
    const response = await this.client.patch(`/api/v1/admin/categories/${id}/toggle-status`);
    return { data: response.data.data };
  }

  // --- ORDERS ---
  async getOrders(params?: Record<string, any>): Promise<{ data: Order[] }> {
    const response = await this.client.get('/api/v1/admin/orders', { params });
    return response.data;
  }

  async getOrder(id: string): Promise<{ data: Order }> {
    const response = await this.client.get(`/api/v1/admin/orders/${id}`);
    return response.data;
  }

  async getOrderStats(): Promise<{ data: OrderStats }> {
    const response = await this.client.get('/api/v1/admin/orders/stats');
    return response.data;
  }

  async updateOrderStatus(id: string, status: string): Promise<{ data: Order }> {
    const response = await this.client.patch(`/api/v1/admin/orders/${id}/status`, { status });
    return response.data;
  }

  async updateShipmentTracking(
    id: string, 
    data: { tracking_number: string; courier_name?: string }
  ): Promise<{ data: Order }> {
    const response = await this.client.patch(`/api/v1/admin/orders/${id}/shipping`, data);
    return response.data;
  }

  // --- PAYMENTS ---
  async getPayments(params?: Record<string, any>): Promise<{ data: Payment[] }> {
    const response = await this.client.get('/api/v1/admin/payments', { params });
    return response.data;
  }

  async getPayment(id: number): Promise<{ data: Payment }> {
    const response = await this.client.get(`/api/v1/admin/payments/${id}`);
    return response.data;
  }

  async verifyPayment(id: number): Promise<{ data: Payment }> {
    const response = await this.client.post(`/api/v1/admin/payments/${id}/verify`);
    return response.data;
  }

  async rejectPayment(id: number): Promise<{ data: Payment }> {
    const response = await this.client.post(`/api/v1/admin/payments/${id}/reject`);
    return response.data;
  }

  // --- SHIPPING ---
  async getShipments(params?: Record<string, any>): Promise<{ data: Shipment[] }> {
    const response = await this.client.get('/api/v1/admin/shipments', { params });
    return response.data;
  }

  async getShipmentStats(): Promise<{ data: ShipmentStats }> {
    const response = await this.client.get('/api/v1/admin/shipments/stats');
    return response.data;
  }

  async getShipment(id: string): Promise<{ data: Shipment }> {
    const response = await this.client.get(`/api/v1/admin/shipments/${id}`);
    return response.data;
  }

  async updateShipmentStatus(id: string, status: string): Promise<{ data: Shipment }> {
    const response = await this.client.patch(`/api/v1/admin/shipments/${id}/status`, { status });
    return response.data;
  }

  // --- LIVE STREAMING ---
  async getLiveStreamStats() {
    const response = await this.client.get('/api/v1/admin/streaming/stats');
    return response.data;
  }

  async getActiveStream() {
    const response = await this.client.get('/api/v1/admin/streaming/active');
    return response.data;
  }

  async getLiveStreamHistory() {
    const response = await this.client.get('/api/v1/admin/streaming');
    return response.data;
  }

  async startLiveStream(data: { title: string; description?: string }) {
    const response = await this.client.post('/api/v1/admin/streaming/start', data);
    return response.data;
  }

  async endLiveStream() {
    const response = await this.client.post('/api/v1/admin/streaming/end');
    return response.data;
  }

  async getLiveVouchers() {
    const response = await this.client.get('/api/v1/admin/streaming/vouchers');
    return response.data;
  }

  async getLiveOrders() {
    const response = await this.client.get('/api/v1/admin/streaming/orders');
    return response.data;
  }

  async getStreamToken(streamTitle: string) {
    const response = await this.client.post('/api/v1/admin/streaming/token', { stream_title: streamTitle });
    return response.data;
  }

  async confirmLiveOrder(orderId: string) {
    const response = await this.client.post(`/api/v1/admin/streaming/orders/${orderId}/confirm`);
    return response.data;
  }

  async updateLiveOrderStatus(orderId: string, status: string) {
    const response = await this.client.patch(`/api/v1/admin/streaming/orders/${orderId}/status`, { status });
    return response.data;
  }

  async getLiveComments(streamId?: string) {
    const params = streamId ? { stream_id: streamId } : {};
    const response = await this.client.get('/api/v1/admin/streaming/comments', { params });
    return response.data;
  }

  async deleteLiveComment(commentId: number) {
    const response = await this.client.delete(`/api/v1/admin/streaming/comments/${commentId}`);
    return response.data;
  }

  async pinProductToStream(streamId: string, productId: number) {
    const response = await this.client.post('/api/v1/admin/streaming/pin-product', {
      live_stream_id: streamId,
      product_id: productId
    });
    return response.data;
  }

  async unpinProductFromStream(streamId: string) {
    const response = await this.client.post('/api/v1/admin/streaming/unpin-product', {
      live_stream_id: streamId
    });
    return response.data;
  }

  async saveStreamAnalytics(streamId: string, data: { total_comments: number; active_users: number }) {
    const response = await this.client.post('/api/v1/admin/streaming/analytics', {
      live_stream_id: streamId,
      ...data
    });
    return response.data;
  }

  async deleteLiveStream(streamId: string) {
    const response = await this.client.delete(`/api/v1/admin/streaming/${streamId}`);
    return response.data;
  }

  async updateLiveStream(streamId: string, data: { title: string; description?: string }) {
    const response = await this.client.put(`/api/v1/admin/streaming/${streamId}`, data);
    return response.data;
  }

  // --- ADMIN MANAGEMENT ---
  async getAdmins(params?: Record<string, any>) {
    const response = await this.client.get('/admin/admins', { params });
    return response.data;
  }

  async getAdmin(id: number) {
    const response = await this.client.get(`/admin/admins/${id}`);
    return response.data;
  }

  async createAdmin(data: Record<string, any>) {
    const response = await this.client.post('/admin/admins', data);
    return response.data;
  }

  async updateAdmin(id: number, data: Record<string, any>) {
    const response = await this.client.put(`/admin/admins/${id}`, data);
    return response.data;
  }

  async deleteAdmin(id: number) {
    const response = await this.client.delete(`/admin/admins/${id}`);
    return response.data;
  }

  async toggleAdminStatus(id: number) {
    const response = await this.client.patch(`/admin/admins/${id}/toggle-status`);
    return response.data;
  }

  async getAdminPermissions() {
    const response = await this.client.get('/admin/permissions');
    return response.data;
  }

  async getAdminActivities(id: number, params?: Record<string, any>) {
    const response = await this.client.get(`/admin/admins/${id}/activities`, { params });
    return response.data;
  }

  // --- SETTINGS ---
  async getSettings() {
    const response = await this.client.get('/api/v1/admin/settings');
    return response.data;
  }

  async getSettingsByGroup(group: string) {
    const response = await this.client.get(`/api/v1/admin/settings/group/${group}`);
    return response.data;
  }

  async updateSettings(data: { settings: { key: string; value: any }[] }) {
    const response = await this.client.post('/api/v1/admin/settings/batch', data);
    return response.data;
  }

  async createSetting(data: { key: string; value: any; type: string; group: string; description?: string }) {
    const response = await this.client.post('/api/v1/admin/settings', data);
    return response.data;
  }

  async updateSetting(key: string, data: { value: any; type?: string; group?: string; description?: string }) {
    const response = await this.client.put(`/api/v1/admin/settings/${key}`, data);
    return response.data;
  }

  async deleteSetting(key: string) {
    const response = await this.client.delete(`/api/v1/admin/settings/${key}`);
    return response.data;
  }

  async updatePaymentSettings(data: Record<string, any>) {
    const response = await this.client.post('/api/v1/admin/settings/payment', data);
    return response.data;
  }

  // --- Payment Settings ---
  async getPaymentSettings() {
    const response = await this.client.get('/api/v1/admin/settings/payment');
    return response.data;
  }

  async createPaymentSetting(data: FormData) {
    const response = await this.client.post('/api/v1/admin/settings/payment', data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async updatePaymentSetting(id: number, data: FormData) {
    // Add _method field for Laravel to handle it as PUT request
    data.append('_method', 'PUT');
    
    const response = await this.client.post(`/api/v1/admin/settings/payment/${id}`, data, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async deletePaymentSetting(id: number) {
    const response = await this.client.delete(`/api/v1/admin/settings/payment/${id}`);
    return response.data;
  }

  async togglePaymentSettingStatus(id: number) {
    const response = await this.client.patch(`/api/v1/admin/settings/payment/${id}/toggle-status`);
    return response.data;
  }

  // --- Payment Proofs ---
  async getPaymentProofs() {
    const response = await this.client.get('/api/v1/admin/payment-proofs');
    return response.data;
  }

  async verifyPaymentProof(id: number) {
    const response = await this.client.put(`/api/v1/admin/payment-proofs/${id}/verify`);
    return response.data;
  }

  async rejectPaymentProof(id: number, notes: string) {
    const response = await this.client.put(`/api/v1/admin/payment-proofs/${id}/reject`, { notes });
    return response.data;
  }

  // --- WHATSAPP ---
  async getWhatsAppMessages(params?: Record<string, any>) {
    const response = await this.client.get('/admin/whatsapp', { params });
    return response.data;
  }

  async sendWhatsAppMessage(data: { phone_number: string; message: string; user_id?: number; order_id?: number; metadata?: any }) {
    const response = await this.client.post('/admin/whatsapp', data);
    return response.data;
  }

  async getWhatsAppMessage(id: number) {
    const response = await this.client.get(`/admin/whatsapp/${id}`);
    return response.data;
  }

  async getWhatsAppStats() {
    const response = await this.client.get('/admin/whatsapp/stats');
    return response.data;
  }

  // Auto Replies
  async getWhatsAppAutoReplies(activeOnly?: boolean) {
    const params = activeOnly ? { active: true } : {};
    const response = await this.client.get('/admin/whatsapp/auto-replies', { params });
    return response.data;
  }

  async createWhatsAppAutoReply(data: { keyword: string; response: string; is_regex?: boolean; is_active?: boolean }) {
    const response = await this.client.post('/admin/whatsapp/auto-replies', data);
    return response.data;
  }

  async updateWhatsAppAutoReply(id: number, data: { keyword: string; response: string; is_regex?: boolean; is_active?: boolean }) {
    const response = await this.client.put(`/admin/whatsapp/auto-replies/${id}`, data);
    return response.data;
  }

  async deleteWhatsAppAutoReply(id: number) {
    const response = await this.client.delete(`/admin/whatsapp/auto-replies/${id}`);
    return response.data;
  }

  async toggleWhatsAppAutoReply(id: number) {
    const response = await this.client.patch(`/admin/whatsapp/auto-replies/${id}/toggle`);
    return response.data;
  }

  // --- USER MANAGEMENT ---
  async getUsers(params?: Record<string, any>) {
    const response = await this.client.get('/admin/users', { params });
    return response.data;
  }

  async getUser(id: number) {
    const response = await this.client.get(`/admin/users/${id}`);
    return response.data;
  }

  async createUser(data: Record<string, any>) {
    const response = await this.client.post('/admin/users', data);
    return response.data;
  }

  async updateUser(id: number, data: Record<string, any>) {
    const response = await this.client.put(`/admin/users/${id}`, data);
    return response.data;
  }

  async deleteUser(id: number) {
    const response = await this.client.delete(`/admin/users/${id}`);
    return response.data;
  }

  async toggleUserStatus(id: number) {
    const response = await this.client.patch(`/admin/users/${id}/toggle-status`);
    return response.data;
  }

  async getUserStats() {
    const response = await this.client.get('/admin/users/stats');
    return response.data;
  }

  // --- ORDER COMPLAINTS ---
  async getOrderComplaints(params?: Record<string, any>): Promise<{ data: OrderComplaint[] }> {
    const response = await this.client.get('/admin/order-complaints', { params });
    return response.data;
  }

  async getOrderComplaintStats(): Promise<{ data: OrderComplaintStats }> {
    const response = await this.client.get('/admin/order-complaints/stats');
    return response.data;
  }

  async getOrderComplaint(id: number): Promise<{ data: OrderComplaint }> {
    const response = await this.client.get(`/admin/order-complaints/${id}`);
    return response.data;
  }

  async resolveOrderComplaint(id: number, notes?: string): Promise<{ data: OrderComplaint }> {
    const response = await this.client.put(`/admin/order-complaints/${id}/resolve`, { notes });
    return response.data;
  }

  async rejectOrderComplaint(id: number, notes: string): Promise<{ data: OrderComplaint }> {
    const response = await this.client.put(`/admin/order-complaints/${id}/reject`, { notes });
    return response.data;
  }

  async processOrderComplaint(id: number): Promise<{ data: OrderComplaint }> {
    const response = await this.client.put(`/admin/order-complaints/${id}/process`);
    return response.data;
  }
}

export const api = new Api();
