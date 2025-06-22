import React, { useState, useEffect } from 'react';
import {
  Box,
  Paper,
  Typography,
  Grid,
  Card,
  CardContent,
  CardMedia,
  Button,
  IconButton,
  TextField,
  InputAdornment,
  Stack,
  Chip,
  Menu,
  MenuItem,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControl,
  InputLabel,
  Select,
  CircularProgress,
  Alert,
} from '@mui/material';
import { useSnackbar } from 'notistack';
import {
  Add as AddIcon,
  Search as SearchIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  FilterList as FilterListIcon,
  CloudUpload as CloudUploadIcon,
  MoreVert as MoreVertIcon,
} from '@mui/icons-material';
import { useNavigate } from 'react-router-dom';
import { api, Product, ProductCategory } from '../../lib/api';

interface ProductWithMenu extends Product {
  id: number;
  name: string;
  category: string;
  price: number;
  stock: number;
  image: string | null;
  status: string;
}

interface FilterMenuProps {
  anchorEl: null | HTMLElement;
  onClose: () => void;
  onFilter: (category: string) => void;
  categories: ProductCategory[];
}

const FilterMenu = ({ anchorEl, onClose, onFilter, categories }: FilterMenuProps) => (
  <Menu
    anchorEl={anchorEl}
    open={Boolean(anchorEl)}
    onClose={onClose}
    anchorOrigin={{
      vertical: 'bottom',
      horizontal: 'right',
    }}
    transformOrigin={{
      vertical: 'top',
      horizontal: 'right',
    }}
  >
    <MenuItem onClick={() => { onFilter('all'); onClose(); }}>
      Semua Kategori
    </MenuItem>
    {categories.map((category) => (
      <MenuItem key={category.id} onClick={() => { onFilter(category.name); onClose(); }}>
        {category.name}
      </MenuItem>
    ))}
  </Menu>
);

interface DeleteConfirmDialogProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
}

const DeleteConfirmDialog = ({ open, onClose, onConfirm }: DeleteConfirmDialogProps) => (
  <Dialog open={open} onClose={onClose}>
    <DialogTitle>Konfirmasi Hapus</DialogTitle>
    <DialogContent>
      Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose}>Batal</Button>
      <Button
        onClick={onConfirm}
        color="error"
        variant="contained"
      >
        Hapus
      </Button>
    </DialogActions>
  </Dialog>
);

export const ProductsPage = () => {
  const navigate = useNavigate();
  const { enqueueSnackbar } = useSnackbar();
  const [products, setProducts] = useState<Product[]>([]);
  const [categories, setCategories] = useState<ProductCategory[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [filterAnchorEl, setFilterAnchorEl] = useState<null | HTMLElement>(null);
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState<ProductWithMenu | null>(null);
  const [menuAnchorEl, setMenuAnchorEl] = useState<{
    [key: number]: HTMLElement | null;
  }>({});

  useEffect(() => {
    Promise.all([
      fetchProducts(),
      fetchCategories()
    ]);
  }, []);

  const fetchCategories = async () => {
    try {
      const response = await api.getCategories();
      setCategories(response.data);
    } catch (err: any) {
      console.error('Error fetching categories:', err);
    }
  };

  const fetchProducts = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await api.getProducts();
      setProducts(response.data);
    } catch (err: any) {
      console.error('Error fetching products:', err);
      setError(err.message || 'Gagal mengambil data produk');
      setProducts([]);
    } finally {
      setLoading(false);
    }
  };

  const handleFilterClick = (event: React.MouseEvent<HTMLElement>) => {
    setFilterAnchorEl(event.currentTarget);
  };

  const handleFilterClose = () => {
    setFilterAnchorEl(null);
  };

  const handleFilter = (category: string) => {
    setSelectedCategory(category);
  };

  const handleMenuOpen = (event: React.MouseEvent<HTMLElement>, productId: number) => {
    setMenuAnchorEl({ ...menuAnchorEl, [productId]: event.currentTarget });
  };

  const handleMenuClose = (productId: number) => {
    setMenuAnchorEl({ ...menuAnchorEl, [productId]: null });
  };

  const handleEdit = (productId: number) => {
    navigate(`/products/${productId}/edit`);
  };

  const handleDelete = (product: Product) => {
    setSelectedProduct(product);
    setDeleteDialogOpen(true);
  };

  const handleDeleteConfirm = async () => {
    if (!selectedProduct) return;
    
    try {
      setLoading(true);
      const response = await api.deleteProduct(selectedProduct.id);
      await fetchProducts(); // Refresh the list
      setDeleteDialogOpen(false);
      setSelectedProduct(null);
      // Show success message
      enqueueSnackbar(response.message || 'Produk berhasil dihapus', { 
        variant: 'success',
        autoHideDuration: 3000
      });
    } catch (err: any) {
      console.error('Error deleting product:', err);
      setError(err.message || 'Gagal menghapus produk');
      // Show error message
      enqueueSnackbar(err.message || 'Gagal menghapus produk', { 
        variant: 'error',
        autoHideDuration: 5000
      });
    } finally {
      setLoading(false);
    }
  };

  const filteredProducts = (products || []).filter((product) => {
    const matchesSearch = product.name
      .toLowerCase()
      .includes(searchQuery.toLowerCase());
    const matchesCategory =
      selectedCategory === 'all' || product.category === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '50vh' }}>
        <CircularProgress />
      </Box>
    );
  }

  if (error) {
    return (
      <Box sx={{ p: 3 }}>
        <Alert severity="error">{error}</Alert>
      </Box>
    );
  }

  return (
    <Box>
      <Box sx={{ mb: 4, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Box>
          <Typography variant="h4">Produk</Typography>
          <Button
            onClick={() => navigate('/categories')}
            sx={{ mt: 1, textTransform: 'none', color: 'text.secondary' }}
          >
            Kelola Kategori
          </Button>
        </Box>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => navigate('/products/create')}
          sx={{
            backgroundColor: 'black',
            '&:hover': {
              backgroundColor: '#333',
            },
          }}
        >
          Tambah Produk
        </Button>
      </Box>

      <Paper sx={{ p: 3, mb: 3 }}>
        <Grid container spacing={2} alignItems="center">
          <Grid item xs={12} md={6}>
            <TextField
              fullWidth
              placeholder="Cari produk..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
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
            <Box sx={{ display: 'flex', justifyContent: 'flex-end' }}>
              <Button
                startIcon={<FilterListIcon />}
                onClick={handleFilterClick}
                sx={{ color: 'black' }}
              >
                Filter
              </Button>
            </Box>
          </Grid>
        </Grid>
      </Paper>

      <Grid container spacing={3}>
        {filteredProducts.map((product) => (
          <Grid item xs={12} sm={6} md={4} lg={3} key={product.id}>
            <Card>
              <CardMedia
                component="img"
                height="200"
                image={product.image || '/placeholder-image.jpg'}
                alt={product.name}
              />
              <CardContent>
                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                  <Box>
                    <Typography variant="h6" noWrap>
                      {product.name}
                    </Typography>
                    <Typography variant="body2" color="text.secondary" gutterBottom>
                      {product.category}
                    </Typography>
                    <Typography variant="subtitle1" sx={{ fontWeight: 'bold' }}>
                      Rp {Number(product.price).toLocaleString()}
                    </Typography>
                    <Stack direction="row" spacing={1} sx={{ mt: 1 }}>
                      <Chip
                        size="small"
                        label={`Stok: ${product.stock}`}
                        color={product.stock > 0 ? 'success' : 'error'}
                      />
                      <Chip
                        size="small"
                        label={product.status}
                        color={product.status === 'active' ? 'primary' : 'default'}
                      />
                    </Stack>
                  </Box>
                  <IconButton
                    size="small"
                    onClick={(e) => handleMenuOpen(e, product.id)}
                  >
                    <MoreVertIcon />
                  </IconButton>
                </Box>
              </CardContent>
              <Menu
                anchorEl={menuAnchorEl[product.id]}
                open={Boolean(menuAnchorEl[product.id])}
                onClose={() => handleMenuClose(product.id)}
              >
                <MenuItem onClick={() => {
                  handleEdit(product.id);
                  handleMenuClose(product.id);
                }}>
                  <EditIcon sx={{ mr: 1 }} /> Edit
                </MenuItem>
                <MenuItem
                  onClick={() => {
                    handleDelete(product);
                    handleMenuClose(product.id);
                  }}
                  sx={{ color: 'error.main' }}
                >
                  <DeleteIcon sx={{ mr: 1 }} /> Hapus
                </MenuItem>
              </Menu>
            </Card>
          </Grid>
        ))}
      </Grid>

      <FilterMenu
        anchorEl={filterAnchorEl}
        onClose={handleFilterClose}
        onFilter={handleFilter}
        categories={categories}
      />

      <DeleteConfirmDialog
        open={deleteDialogOpen}
        onClose={() => setDeleteDialogOpen(false)}
        onConfirm={handleDeleteConfirm}
      />
    </Box>
  );
};
