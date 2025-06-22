import { useState, useEffect, useCallback } from 'react';
import Echo from '@/lib/echo';
import { LiveComment, NewCommentEvent, CommentDeletedEvent } from '@/types/stream';
import {
  Box,
  Paper,
  Typography,
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
  Stack,
  Button,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Card,
  CardContent,
  CircularProgress,
} from '@mui/material';
import {
  Search,
  Delete,
  Comment,
  ShoppingCart,
  Warning,
} from '@mui/icons-material';
import { api } from '@/lib/api';

export const LiveCommentsPage = () => {
  const [comments, setComments] = useState<LiveComment[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string>('');
  const [searchQuery, setSearchQuery] = useState('');
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [selectedComment, setSelectedComment] = useState<LiveComment | null>(null);

  const fetchComments = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const response = await api.getLiveComments();
      setComments(response.data);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch live comments');
    } finally {
      setLoading(false);
    }
  }, []);

  // Initial fetch
  useEffect(() => {
    fetchComments();
  }, [fetchComments]);

  // Real-time updates
  useEffect(() => {
    const channel = Echo.channel('live-comments');
    
    channel.listen('NewComment', (e: NewCommentEvent) => {
      setComments(prev => [e.comment, ...prev]);
    });

    channel.listen('CommentDeleted', (e: CommentDeletedEvent) => {
      setComments(prev => prev.filter(comment => comment.id !== e.commentId));
    });

    return () => {
      channel.stopListening('NewComment');
      channel.stopListening('CommentDeleted');
    };
  }, []);

  const handleDeleteComment = async (commentId: number) => {
    try {
      await api.deleteLiveComment(commentId);
      setComments(prev => prev.filter(comment => comment.id !== commentId));
      setDeleteDialogOpen(false);
      setSelectedComment(null);
    } catch (err: any) {
      console.error('Failed to delete comment:', err);
    }
  };

  const openDeleteDialog = (comment: LiveComment) => {
    setSelectedComment(comment);
    setDeleteDialogOpen(true);
  };

  // Stats calculation
  const stats = {
    totalComments: comments.length,
    orderComments: comments.filter(comment => comment.type === 'ORDER').length,
    regularComments: comments.filter(comment => comment.type === 'CHAT').length,
    flaggedComments: comments.filter(comment => comment.is_flagged).length,
  };

  const filteredComments = comments.filter(comment =>
    comment.content.toLowerCase().includes(searchQuery.toLowerCase()) ||
    comment.user.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 4 }}>
        Live Comments Management
      </Typography>

      <Box sx={{ display: 'flex', gap: 3, mb: 4 }}>
        <Card sx={{ minWidth: 200 }}>
          <CardContent>
            <Stack direction="row" alignItems="center" spacing={2}>
              <Comment sx={{ fontSize: 40, color: 'primary.main' }} />
              <Box>
                <Typography variant="h4">{stats.totalComments}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Total Komentar
                </Typography>
              </Box>
            </Stack>
          </CardContent>
        </Card>

        <Card sx={{ minWidth: 200 }}>
          <CardContent>
            <Stack direction="row" alignItems="center" spacing={2}>
              <ShoppingCart sx={{ fontSize: 40, color: 'success.main' }} />
              <Box>
                <Typography variant="h4">{stats.orderComments}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Komentar Order
                </Typography>
              </Box>
            </Stack>
          </CardContent>
        </Card>

        <Card sx={{ minWidth: 200 }}>
          <CardContent>
            <Stack direction="row" alignItems="center" spacing={2}>
              <Warning sx={{ fontSize: 40, color: 'warning.main' }} />
              <Box>
                <Typography variant="h4">{stats.flaggedComments}</Typography>
                <Typography variant="body2" color="text.secondary">
                  Komentar Bermasalah
                </Typography>
              </Box>
            </Stack>
          </CardContent>
        </Card>
      </Box>

      <Paper sx={{ p: 3 }}>
        <Box sx={{ mb: 3 }}>
          <TextField
            fullWidth
            placeholder="Cari komentar atau username..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <Search />
                </InputAdornment>
              ),
            }}
          />
        </Box>

        {loading ? (
          <Box sx={{ display: 'flex', justifyContent: 'center', p: 3 }}>
            <CircularProgress />
          </Box>
        ) : error ? (
          <Typography color="error" align="center" sx={{ p: 3 }}>
            {error}
          </Typography>
        ) : (
          <TableContainer>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>User</TableCell>
                  <TableCell>Komentar</TableCell>
                  <TableCell>Tipe</TableCell>
                  <TableCell>Waktu</TableCell>
                  <TableCell>Status</TableCell>
                  <TableCell align="right">Aksi</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {filteredComments.map((comment) => (
                  <TableRow key={comment.id}>
                    <TableCell>
                      <Stack>
                        <Typography variant="body2">{comment.user.name}</Typography>
                        <Typography variant="caption" color="text.secondary">
                          ID: {comment.user.id}
                        </Typography>
                      </Stack>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2" sx={{ maxWidth: 300 }}>
                        {comment.content}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Chip
                        size="small"
                        label={comment.type}
                        color={comment.type === 'ORDER' ? 'success' : 'default'}
                      />
                    </TableCell>
                    <TableCell>
                      {new Date(comment.created_at).toLocaleString('id-ID')}
                    </TableCell>
                    <TableCell>
                      {comment.is_flagged && (
                        <Chip
                          size="small"
                          label="Flagged"
                          color="warning"
                        />
                      )}
                    </TableCell>
                    <TableCell align="right">
                      <IconButton
                        size="small"
                        onClick={() => openDeleteDialog(comment)}
                        sx={{ color: 'error.main' }}
                      >
                        <Delete />
                      </IconButton>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        )}
      </Paper>

      {/* Delete Confirmation Dialog */}
      <Dialog
        open={deleteDialogOpen}
        onClose={() => setDeleteDialogOpen(false)}
      >
        <DialogTitle>Hapus Komentar</DialogTitle>
        <DialogContent>
          <Typography>
            Apakah Anda yakin ingin menghapus komentar dari {selectedComment?.user?.name}?
          </Typography>
          <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
            "{selectedComment?.content}"
          </Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setDeleteDialogOpen(false)}>
            Batal
          </Button>
          <Button
            onClick={() => handleDeleteComment(selectedComment?.id)}
            color="error"
            variant="contained"
          >
            Hapus
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};
