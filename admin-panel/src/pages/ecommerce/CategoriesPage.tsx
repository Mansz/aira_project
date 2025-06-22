import React from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Box,
  Paper,
  Typography,
  Grid,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip,
  IconButton,
  TextField,
  InputAdornment,
  FormControl,
  Select,
  MenuItem,
  Avatar,
  CircularProgress,
  Alert,
  Button,
  SelectChangeEvent,
} from '@mui/material';
import {
  Add as AddIcon,
  Search as SearchIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Visibility as VisibilityIcon,
  VisibilityOff as VisibilityOffIcon,
  Image as ImageIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import { api, ProductCategory } from '../../lib/api';

type StatusFilterType = 'all' | 'active' | 'inactive';

const CategoriesPage: React.FC = () => {
  const navigate = useNavigate();
  const { enqueueSnackbar } = useSnackbar();
  const [categories, setCategories] = React.useState<ProductCategory[]>([]);
  const [loading, setLoading] = React.useState(true);
  const [searchTerm, setSearchTerm] = React.useState('');
  const [statusFilter, setStatusFilter] = React.useState<StatusFilterType>('all');
  const [error, setError] = React.useState<string | null>(null);

  const fetchCategories = React.useCallback(async () => {
    try {
      setLoading(true);
      const params: Record<string, any> = {};
      
      if (searchTerm) {
        params.search = searchTerm;
      }
      
      if (statusFilter !== 'all') {
        params.status = statusFilter;
      }

      const response = await api.getCategories(params);
      setCategories(response.data);
      setError(null);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch categories');
      enqueueSnackbar(err.message || 'Failed to fetch categories', { variant: 'error' });
    } finally {
      setLoading(false);
    }
  }, [searchTerm, statusFilter, enqueueSnackbar]);

  React.useEffect(() => {
    fetchCategories();
  }, [fetchCategories]);

  const handleEdit = (category: ProductCategory) => {
    navigate(`/categories/${category.id}/edit`);
  };

  const handleDelete = async (id: number) => {
    if (window.confirm('Are you sure you want to delete this category?')) {
      try {
        await api.deleteCategory(id);
        fetchCategories();
        setError(null);
        enqueueSnackbar('Category deleted successfully', { variant: 'success' });
      } catch (err: any) {
        setError(err.message || 'Failed to delete category');
        enqueueSnackbar(err.message || 'Failed to delete category', { variant: 'error' });
      }
    }
  };

  const handleToggleStatus = async (category: ProductCategory) => {
    try {
      await api.toggleCategoryStatus(category.id);
      fetchCategories();
      setError(null);
      enqueueSnackbar(
        `Category ${category.is_active ? 'deactivated' : 'activated'} successfully`, 
        { variant: 'success' }
      );
    } catch (err: any) {
      setError(err.message || 'Failed to toggle category status');
      enqueueSnackbar(err.message || 'Failed to toggle category status', { variant: 'error' });
    }
  };

  const handleStatusFilterChange = (event: SelectChangeEvent<StatusFilterType>) => {
    setStatusFilter(event.target.value as StatusFilterType);
  };

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" alignItems="center" minHeight="200px">
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box p={3}>
      <Box mb={4}>
        <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
          <Box>
            <Typography variant="h4" gutterBottom>
              Product Categories
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Manage your product categories
            </Typography>
          </Box>
          <Button
            variant="contained"
            startIcon={<AddIcon />}
            onClick={() => navigate('/categories/create')}
          >
            Add Category
          </Button>
        </Box>

        {error && (
          <Alert severity="error" sx={{ mb: 2 }}>
            {error}
          </Alert>
        )}

        <Paper sx={{ p: 2, mb: 3 }}>
          <Grid container spacing={2} alignItems="center">
            <Grid item xs={12} md={6}>
              <TextField
                fullWidth
                placeholder="Search categories..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <SearchIcon />
                    </InputAdornment>
                  ),
                }}
              />
            </Grid>
            <Grid item xs={12} md={6}>
              <FormControl fullWidth>
                <Select<StatusFilterType>
                  value={statusFilter}
                  onChange={handleStatusFilterChange}
                >
                  <MenuItem value="all">All Status</MenuItem>
                  <MenuItem value="active">Active</MenuItem>
                  <MenuItem value="inactive">Inactive</MenuItem>
                </Select>
              </FormControl>
            </Grid>
          </Grid>
        </Paper>

        <TableContainer component={Paper}>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Category</TableCell>
                <TableCell>Description</TableCell>
                <TableCell>Products</TableCell>
                <TableCell>Status</TableCell>
                <TableCell align="right">Actions</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {categories.length === 0 ? (
                <TableRow>
                  <TableCell colSpan={5} align="center">
                    No categories found
                  </TableCell>
                </TableRow>
              ) : (
                categories.map((category) => (
                  <TableRow key={category.id}>
                    <TableCell>
                      <Box display="flex" alignItems="center">
                        <Avatar
                          src={category.icon || undefined}
                          sx={{ width: 40, height: 40, mr: 2 }}
                        >
                          <ImageIcon />
                        </Avatar>
                        <Box>
                          <Typography variant="subtitle2">{category.name}</Typography>
                          <Typography variant="body2" color="text.secondary">
                            {category.slug}
                          </Typography>
                        </Box>
                      </Box>
                    </TableCell>
                    <TableCell>{category.description || '-'}</TableCell>
                    <TableCell>
                      <Chip
                        label={`${category.products_count} products`}
                        color="primary"
                        size="small"
                      />
                    </TableCell>
                    <TableCell>
                      <Chip
                        icon={category.is_active ? <VisibilityIcon /> : <VisibilityOffIcon />}
                        label={category.is_active ? 'Active' : 'Inactive'}
                        color={category.is_active ? 'success' : 'default'}
                        onClick={() => handleToggleStatus(category)}
                        size="small"
                      />
                    </TableCell>
                    <TableCell align="right">
                      <IconButton
                        size="small"
                        onClick={() => handleEdit(category)}
                        sx={{ mr: 1 }}
                      >
                        <EditIcon />
                      </IconButton>
                      <IconButton
                        size="small"
                        onClick={() => handleDelete(category.id)}
                        disabled={category.products_count > 0}
                        color="error"
                      >
                        <DeleteIcon />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                ))
              )}
            </TableBody>
          </Table>
        </TableContainer>
      </Box>
    </Box>
  );
};

export default CategoriesPage;
