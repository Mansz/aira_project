import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Box,
  Paper,
  Typography,
  TextField,
  Button,
  FormControlLabel,
  Checkbox,
  Avatar,
  Stack,
  Alert,
  CircularProgress,
  Breadcrumbs,
  Link,
} from '@mui/material';
import {
  ArrowBack as ArrowBackIcon,
  Save as SaveIcon,
  Image as ImageIcon,
} from '@mui/icons-material';
import { useSnackbar } from 'notistack';
import { api, ProductCategory } from '../../lib/api';

const CategoryForm: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { enqueueSnackbar } = useSnackbar();
  const isEdit = Boolean(id);

  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [category, setCategory] = useState<ProductCategory | null>(null);

  const [formData, setFormData] = useState({
    name: '',
    description: '',
    is_active: true,
  });
  const [iconFile, setIconFile] = useState<File | null>(null);
  const [iconPreview, setIconPreview] = useState<string | null>(null);
  const [errors, setErrors] = useState<Record<string, string[]>>({});

  useEffect(() => {
    if (isEdit && id) {
      fetchCategory();
    }
  }, [id, isEdit]);

  const fetchCategory = async () => {
    try {
      setLoading(true);
      const response = await api.getCategory(parseInt(id!));
      const categoryData = response.data;
      
      setCategory(categoryData);
      setFormData({
        name: categoryData.name,
        description: categoryData.description || '',
        is_active: categoryData.is_active,
      });
      setIconPreview(categoryData.icon);
      setError(null);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch category');
      enqueueSnackbar('Failed to fetch category', { variant: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});

    try {
      const formDataToSend = new FormData();
      formDataToSend.append('name', formData.name);
      formDataToSend.append('description', formData.description);
      formDataToSend.append('is_active', formData.is_active ? '1' : '0');
      
      if (iconFile) {
        formDataToSend.append('icon', iconFile);
      }

      if (isEdit && id) {
        await api.updateCategory(parseInt(id), formDataToSend);
        enqueueSnackbar('Category updated successfully', { variant: 'success' });
      } else {
        await api.createCategory(formDataToSend);
        enqueueSnackbar('Category created successfully', { variant: 'success' });
      }

      navigate('/categories');
    } catch (err: any) {
      if (err.errors) {
        setErrors(err.errors);
      }
      setError(err.message || 'Failed to save category');
      enqueueSnackbar(err.message || 'Failed to save category', { variant: 'error' });
    } finally {
      setSubmitting(false);
    }
  };

  const handleIconChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      // Validate file size (max 2MB)
      if (file.size > 2 * 1024 * 1024) {
        enqueueSnackbar('Image size must be less than 2MB', { variant: 'error' });
        return;
      }

      // Validate file type
      if (!file.type.startsWith('image/')) {
        enqueueSnackbar('Please select a valid image file', { variant: 'error' });
        return;
      }

      setIconFile(file);
      const reader = new FileReader();
      reader.onload = (e) => {
        setIconPreview(e.target?.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const removeIcon = () => {
    setIconFile(null);
    setIconPreview(null);
  };

  if (loading) {
    return (
      <Box display="flex" justifyContent="center" alignItems="center" minHeight="400px">
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box p={3}>
      <Box mb={3}>
        <Breadcrumbs>
          <Link
            component="button"
            variant="body1"
            onClick={() => navigate('/categories')}
            sx={{ textDecoration: 'none' }}
          >
            Categories
          </Link>
          <Typography color="text.primary">
            {isEdit ? 'Edit Category' : 'Add Category'}
          </Typography>
        </Breadcrumbs>
      </Box>

      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Box>
          <Typography variant="h4" gutterBottom>
            {isEdit ? 'Edit Category' : 'Add New Category'}
          </Typography>
          <Typography variant="body2" color="text.secondary">
            {isEdit ? 'Update category information' : 'Create a new product category'}
          </Typography>
        </Box>
        <Button
          variant="outlined"
          startIcon={<ArrowBackIcon />}
          onClick={() => navigate('/categories')}
        >
          Back to Categories
        </Button>
      </Box>

      {error && (
        <Alert severity="error" sx={{ mb: 3 }}>
          {error}
        </Alert>
      )}

      <Paper sx={{ p: 3 }}>
        <form onSubmit={handleSubmit}>
          <Stack spacing={3}>
            <TextField
              label="Name"
              required
              fullWidth
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              error={Boolean(errors.name)}
              helperText={errors.name?.[0]}
            />

            <TextField
              label="Description"
              fullWidth
              multiline
              rows={3}
              value={formData.description}
              onChange={(e) => setFormData({ ...formData, description: e.target.value })}
              error={Boolean(errors.description)}
              helperText={errors.description?.[0]}
            />

            <Box>
              <input
                accept="image/*"
                type="file"
                onChange={handleIconChange}
                style={{ display: 'none' }}
                id="icon-upload"
              />
              <label htmlFor="icon-upload">
                <Button
                  variant="outlined"
                  component="span"
                  startIcon={<ImageIcon />}
                  fullWidth
                >
                  Upload Icon
                </Button>
              </label>
              {iconPreview && (
                <Box mt={2} display="flex" justifyContent="center" alignItems="center" gap={2}>
                  <Avatar
                    src={iconPreview}
                    sx={{ width: 100, height: 100 }}
                    variant="rounded"
                  />
                  <Button
                    variant="outlined"
                    color="error"
                    size="small"
                    onClick={removeIcon}
                  >
                    Remove
                  </Button>
                </Box>
              )}
            </Box>

            <FormControlLabel
              control={
                <Checkbox
                  checked={formData.is_active}
                  onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                />
              }
              label="Active"
            />

            <Box display="flex" gap={2}>
              <Button
                variant="outlined"
                onClick={() => navigate('/categories')}
                disabled={submitting}
              >
                Cancel
              </Button>
              <Button
                type="submit"
                variant="contained"
                startIcon={<SaveIcon />}
                disabled={submitting}
              >
                {submitting ? 'Saving...' : (isEdit ? 'Update' : 'Create')}
              </Button>
            </Box>
          </Stack>
        </form>
      </Paper>
    </Box>
  );
};

export default CategoryForm;
