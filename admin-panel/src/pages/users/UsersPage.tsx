import { useState, useEffect } from 'react';
import {
  Box,
  Button,
  Card,
  IconButton,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TablePagination,
  TextField,
  Typography,
  Avatar,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControlLabel,
  Switch,
  Stack,
  Alert,
} from '@mui/material';
import {
  Add as AddIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Search as SearchIcon,
} from '@mui/icons-material';
import { api } from '@/lib/api';
import { User, UserFilters, UserFormData } from '@/types/user';
import { useSnackbar } from 'notistack';

export default function UsersPage() {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [filters, setFilters] = useState<UserFilters>({
    page: 1,
    per_page: 10,
    sort_field: 'created_at',
    sort_order: 'desc',
  });
  const [total, setTotal] = useState(0);
  const [openDialog, setOpenDialog] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [formData, setFormData] = useState<UserFormData>({
    name: '',
    email: '',
    phone: '',
    whatsapp: '',
    is_active: true,
  });
  const { enqueueSnackbar } = useSnackbar();

  useEffect(() => {
    fetchUsers();
  }, [filters]);

  const fetchUsers = async () => {
    try {
      setLoading(true);
      const response = await api.getUsers(filters);
      console.log('API Response:', response); // Debug log
      
      // Handle different response structures
      if (response.data) {
        setUsers(response.data || []);
        setTotal(response.meta?.total || 0);
      } else {
        setUsers([]);
        setTotal(0);
      }
      setError(null);
    } catch (err) {
      console.error('Fetch users error:', err);
      setError('Failed to fetch users');
      enqueueSnackbar('Failed to fetch users', { variant: 'error' });
      setUsers([]); // Set empty array on error
      setTotal(0);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFilters({ ...filters, search: event.target.value, page: 1 });
  };

  const handlePageChange = (event: unknown, newPage: number) => {
    setFilters({ ...filters, page: newPage + 1 });
  };

  const handleRowsPerPageChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFilters({ ...filters, per_page: parseInt(event.target.value, 10), page: 1 });
  };

  const handleOpenDialog = (user?: User) => {
    if (user) {
      setSelectedUser(user);
      setFormData({
        name: user.name,
        email: user.email,
        phone: user.phone || '',
        whatsapp: user.whatsapp,
        is_active: user.is_active,
      });
    } else {
      setSelectedUser(null);
      setFormData({
        name: '',
        email: '',
        phone: '',
        whatsapp: '',
        is_active: true,
      });
    }
    setOpenDialog(true);
  };

  const handleCloseDialog = () => {
    setOpenDialog(false);
    setSelectedUser(null);
    setFormData({
      name: '',
      email: '',
      phone: '',
      whatsapp: '',
      is_active: true,
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      if (selectedUser) {
      await api.updateUser(selectedUser.id, formData);
        enqueueSnackbar('User updated successfully', { variant: 'success' });
      } else {
        await api.createUser(formData);
        enqueueSnackbar('User created successfully', { variant: 'success' });
      }
      handleCloseDialog();
      fetchUsers();
    } catch (err) {
      enqueueSnackbar('Operation failed', { variant: 'error' });
    }
  };

  const handleDelete = async (user: User) => {
    if (window.confirm('Are you sure you want to delete this user?')) {
      try {
        await api.deleteUser(user.id);
        enqueueSnackbar('User deleted successfully', { variant: 'success' });
        fetchUsers();
      } catch (err) {
        enqueueSnackbar('Failed to delete user', { variant: 'error' });
      }
    }
  };

  const handleToggleStatus = async (user: User) => {
    try {
      await api.toggleUserStatus(user.id);
      enqueueSnackbar('User status updated successfully', { variant: 'success' });
      fetchUsers();
    } catch (err) {
      enqueueSnackbar('Failed to update user status', { variant: 'error' });
    }
  };

  return (
    <Box>
      <Box sx={{ mb: 4, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Typography variant="h4" component="h1">
          Users
        </Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => handleOpenDialog()}
        >
          Add User
        </Button>
      </Box>

      <Card sx={{ mb: 4 }}>
        <Box sx={{ p: 2 }}>
          <TextField
            fullWidth
            placeholder="Search users..."
            InputProps={{
              startAdornment: <SearchIcon sx={{ mr: 1, color: 'text.secondary' }} />,
            }}
            onChange={handleSearch}
          />
        </Box>

        <TableContainer>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>User</TableCell>
                <TableCell>Email</TableCell>
                <TableCell>Phone</TableCell>
                <TableCell>WhatsApp</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Created At</TableCell>
                <TableCell align="right">Actions</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {loading ? (
                <TableRow>
                  <TableCell colSpan={7} align="center">
                    Loading...
                  </TableCell>
                </TableRow>
              ) : users.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={7} align="center">
                    No users found
                  </TableCell>
                </TableRow>
              ) : (
                users.map((user) => (
                  <TableRow key={user.id}>
                    <TableCell>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <Avatar src={user.avatar} alt={user.name} sx={{ mr: 2 }}>
                          {user.name.charAt(0)}
                        </Avatar>
                        <Typography>{user.name}</Typography>
                      </Box>
                    </TableCell>
                    <TableCell>{user.email}</TableCell>
                    <TableCell>{user.phone || '-'}</TableCell>
                    <TableCell>{user.whatsapp}</TableCell>
                    <TableCell>
                      <Chip
                        label={user.is_active ? 'Active' : 'Inactive'}
                        color={user.is_active ? 'success' : 'default'}
                        onClick={() => handleToggleStatus(user)}
                      />
                    </TableCell>
                    <TableCell>
                      {new Date(user.created_at).toLocaleDateString()}
                    </TableCell>
                    <TableCell align="right">
                      <IconButton onClick={() => handleOpenDialog(user)}>
                        <EditIcon />
                      </IconButton>
                      <IconButton onClick={() => handleDelete(user)}>
                        <DeleteIcon />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>

        <TablePagination
          component="div"
          count={total}
          page={filters.page ? filters.page - 1 : 0}
          onPageChange={handlePageChange}
          rowsPerPage={filters.per_page || 10}
          onRowsPerPageChange={handleRowsPerPageChange}
        />
      </Card>

      <Dialog open={openDialog} onClose={handleCloseDialog} maxWidth="sm" fullWidth>
        <form onSubmit={handleSubmit}>
          <DialogTitle>
            {selectedUser ? 'Edit User' : 'Add User'}
          </DialogTitle>
          <DialogContent>
            <Stack spacing={3} sx={{ mt: 2 }}>
              <TextField
                label="Name"
                fullWidth
                required
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              />
              <TextField
                label="Email"
                type="email"
                fullWidth
                required
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              />
              <TextField
                label="Phone"
                fullWidth
                value={formData.phone}
                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
              />
              <TextField
                label="WhatsApp"
                fullWidth
                required
                value={formData.whatsapp}
                onChange={(e) => setFormData({ ...formData, whatsapp: e.target.value })}
              />
              {!selectedUser && (
                <TextField
                  label="Password"
                  type="password"
                  fullWidth
                  required={!selectedUser}
                  value={formData.password || ''}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                />
              )}
              <FormControlLabel
                control={
                  <Switch
                    checked={formData.is_active}
                    onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                  />
                }
                label="Active"
              />
            </Stack>
          </DialogContent>
          <DialogActions>
            <Button onClick={handleCloseDialog}>Cancel</Button>
            <Button type="submit" variant="contained">
              {selectedUser ? 'Update' : 'Create'}
            </Button>
          </DialogActions>
        </form>
      </Dialog>
    </Box>
  );
}
