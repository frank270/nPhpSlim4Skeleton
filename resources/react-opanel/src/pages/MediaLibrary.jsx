import React, { useEffect, useMemo, useState } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../i18n';

const toast = (message, type = 'success') => {
  if (window.showToast) {
    window.showToast(message, type);
  } else {
    // eslint-disable-next-line no-alert
    alert(message);
  }
};

const formatBytes = (bytes) => {
  if (!bytes || Number.isNaN(bytes)) return '0 B';
  const units = ['B', 'KB', 'MB', 'GB'];
  const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
  const value = bytes / (1024 ** i);
  return `${value.toFixed(value >= 10 ? 0 : 1)} ${units[i]}`;
};

const statusOptions = [
  { value: '', label: '全部狀態' },
  { value: 'active', label: '使用中' },
  { value: 'draft', label: '草稿' },
  { value: 'archived', label: '封存' },
];

function MediaLibraryApp({ apiBase }) {
  const { t } = useTranslation();
  const [assets, setAssets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [filters, setFilters] = useState({ keyword: '', status: '' });
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(20);
  const [pagination, setPagination] = useState({ total: 0 });
  const [reloadFlag, setReloadFlag] = useState(0);

  const [uploadForm, setUploadForm] = useState({
    file: null,
    alt_text: '',
    caption: '',
    status: 'active',
  });
  const [uploading, setUploading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(null);
  const [editingValues, setEditingValues] = useState({});
  const [deletingId, setDeletingId] = useState(null);

  const listQuery = useMemo(() => {
    const query = new URLSearchParams({
      page: String(page),
      per_page: String(perPage),
    });
    if (filters.keyword) {
      query.append('keyword', filters.keyword);
    }
    if (filters.status) {
      query.append('status', filters.status);
    }
    return `${apiBase}/list?${query.toString()}`;
  }, [apiBase, filters.keyword, filters.status, page, perPage]);

  const loadAssets = async () => {
    setLoading(true);
    setError('');
    try {
      const response = await fetch(listQuery);
      if (!response.ok) {
        throw new Error(t('common.error'));
      }
      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || t('common.error'));
      }
      setAssets(result.data || []);
      setPagination(result.pagination || { total: 0, page: 1, per_page: perPage });
    } catch (err) {
      setError(err.message || t('common.error'));
      setAssets([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadAssets();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [listQuery, reloadFlag]);

  useEffect(() => {
    const nextValues = {};
    assets.forEach((asset) => {
      nextValues[asset.id] = {
        alt_text: asset.alt_text || '',
        caption: asset.caption || '',
        status: asset.status || 'active',
      };
    });
    setEditingValues(nextValues);
  }, [assets]);

  const totalPages = Math.max(1, Math.ceil((pagination.total || 0) / perPage));

  const handleFilterSubmit = (event) => {
    event.preventDefault();
    setPage(1);
    setReloadFlag((prev) => prev + 1);
  };

  const handleResetFilters = () => {
    setFilters({ keyword: '', status: '' });
    setPage(1);
    setReloadFlag((prev) => prev + 1);
  };

  const handleUploadChange = (event) => {
    const { name, value, files } = event.target;
    if (name === 'file') {
      setUploadForm((prev) => ({ ...prev, file: files?.[0] || null }));
    } else {
      setUploadForm((prev) => ({ ...prev, [name]: value }));
    }
  };

  const submitUpload = async (event) => {
    event.preventDefault();
    if (!uploadForm.file) {
      toast(t('common.error'), 'error');
      return;
    }
    setUploading(true);
    setUploadProgress(0);
    try {
      const formData = new FormData();
      formData.append('file', uploadForm.file);
      if (uploadForm.alt_text) formData.append('alt_text', uploadForm.alt_text);
      if (uploadForm.caption) formData.append('caption', uploadForm.caption);
      if (uploadForm.status) formData.append('status', uploadForm.status);
      formData.append('uploaded_from', 'media-library');

      const result = await new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', `${apiBase}/upload`);
        xhr.upload.onprogress = (e) => {
          if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            setUploadProgress(percent);
          }
        };
        xhr.onload = () => {
          try {
            const payload = JSON.parse(xhr.responseText || '{}');
            if (xhr.status >= 200 && xhr.status < 300 && payload.success) {
              resolve(payload);
            } else {
              reject(new Error(payload.message || t('common.error')));
            }
          } catch (err) {
            reject(err instanceof Error ? err : new Error(t('common.error')));
          }
        };
        xhr.onerror = () => {
           reject(new Error(t('common.error')));
        };
        xhr.send(formData);
      });

      toast(t('common.success'), 'success');
      setUploadForm({
        file: null,
        alt_text: '',
        caption: '',
        status: 'active',
      });
      const fileInput = document.getElementById('media-upload-input');
      if (fileInput) {
        fileInput.value = '';
      }
      setReloadFlag((prev) => prev + 1);
    } catch (err) {
      toast(err.message || t('common.error'), 'error');
    } finally {
      setUploading(false);
        setUploadProgress(null);
    }
  };

  const handleFieldChange = (id, field, value) => {
    setEditingValues((prev) => ({
      ...prev,
      [id]: {
        ...(prev[id] || {}),
        [field]: value,
      },
    }));
  };

  const saveMeta = async (assetId) => {
    const payload = editingValues[assetId] || {};
    try {
      const response = await fetch(`${apiBase}/${assetId}/edit`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || t('common.error'));
      }
      toast(t('common.saved'), 'success');
      setReloadFlag((prev) => prev + 1);
    } catch (err) {
      toast(err.message || t('common.error'), 'error');
    }
  };

  const toggleStatus = async (asset) => {
    const nextStatus = asset.status === 'active' ? 'archived' : 'active';
    try {
      const response = await fetch(`${apiBase}/${asset.id}/toggle-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: nextStatus }),
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || t('common.error'));
      }
      toast(t('common.status_updated'), 'success');
      setReloadFlag((prev) => prev + 1);
    } catch (err) {
      toast(err.message || t('common.error'), 'error');
    }
  };

  const deleteAsset = async (asset) => {
    if (!window.confirm(t('media_library.delete_confirm', { name: asset.original_name }))) {
      return;
    }
    setDeletingId(asset.id);
    try {
      const response = await fetch(`${apiBase}/${asset.id}/delete`, {
        method: 'DELETE',
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || t('common.error'));
      }
      toast(t('common.deleted'), 'success');
      setReloadFlag((prev) => prev + 1);
    } catch (err) {
      toast(err.message || t('common.error'), 'error');
    } finally {
      setDeletingId(null);
    }
  };

  const copyUrl = async (url) => {
    try {
      await navigator.clipboard.writeText(url);
      toast(t('common.success'), 'success');
    } catch {
      toast(t('common.error'), 'warning');
    }
  };

  return (
    <div className="row gy-4">
      <div className="col-12 col-xl-4">
        <div className="card">
          <div className="card-header">
            <h3 className="card-title">{t('media_library.upload_title')}</h3>
          </div>
          <div className="card-body">
            <form onSubmit={submitUpload}>
              <div className="mb-3">
                <label className="form-label" htmlFor="media-upload-input">{t('media_library.select_file')}</label>
                <input
                  id="media-upload-input"
                  type="file"
                  name="file"
                  className="form-control"
                  onChange={handleUploadChange}
                  required
                />
              </div>
              <div className="mb-3">
                <label className="form-label">{t('media_library.alt_text')}</label>
                <input
                  type="text"
                  name="alt_text"
                  value={uploadForm.alt_text}
                  onChange={handleUploadChange}
                  className="form-control"
                  placeholder={t('media_library.alt_text_placeholder')}
                />
              </div>
              <div className="mb-3">
                <label className="form-label">{t('media_library.caption')}</label>
                <textarea
                  name="caption"
                  value={uploadForm.caption}
                  onChange={handleUploadChange}
                  className="form-control"
                  rows="2"
                  placeholder={t('media_library.caption_placeholder')}
                />
              </div>
              <div className="mb-3">
                <label className="form-label">{t('media_library.default_status')}</label>
                <select
                  name="status"
                  className="form-select"
                  value={uploadForm.status}
                  onChange={handleUploadChange}
                >
                  <option value="active">{t('media_library.status_active')}</option>
                  <option value="draft">{t('media_library.status_draft')}</option>
                  <option value="archived">{t('media_library.status_archived')}</option>
                </select>
              </div>
              {uploading && (
                <div className="mb-3">
                  <div className="progress" role="progressbar" aria-valuenow={uploadProgress || 0} aria-valuemin="0" aria-valuemax="100">
                    <div
                      className="progress-bar progress-bar-striped progress-bar-animated"
                      style={{ width: `${uploadProgress || 0}%` }}
                    >
                      {uploadProgress || 0}%
                    </div>
                  </div>
                </div>
              )}
              <div className="d-grid">
                <button type="submit" className="btn btn-primary" disabled={uploading}>
                  {uploading ? t('media_library.uploading') : t('media_library.upload_btn')}
                </button>
              </div>
            </form>
          </div>
        </div>
        <div className="card mt-4">
          <div className="card-header">
            <h3 className="card-title">{t('media_library.filter_title')}</h3>
          </div>
          <div className="card-body">
            <form onSubmit={handleFilterSubmit}>
              <div className="mb-3">
                <label className="form-label">{t('media_library.keyword')}</label>
                <input
                  type="text"
                  className="form-control"
                  placeholder={t('media_library.keyword_placeholder')}
                  value={filters.keyword}
                  onChange={(e) => setFilters((prev) => ({ ...prev, keyword: e.target.value }))}
                />
              </div>
              <div className="mb-3">
                <label className="form-label">{t('common.status')}</label>
                <select
                  className="form-select"
                  value={filters.status}
                  onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
                >
                  {statusOptions.map((option) => (
                    <option key={option.value} value={option.value}>
                      {option.value === '' ? t('common.all_status') : 
                       option.value === 'active' ? t('media_library.status_active') :
                       option.value === 'draft' ? t('media_library.status_draft') :
                       option.value === 'archived' ? t('media_library.status_archived') : option.label}
                    </option>
                  ))}
                </select>
              </div>
              <div className="d-flex gap-2">
                <button type="submit" className="btn btn-primary w-50">{t('media_library.apply')}</button>
                <button type="button" className="btn btn-light w-50" onClick={handleResetFilters}>{t('media_library.reset')}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div className="col-12 col-xl-8">
        <div className="card">
          <div className="card-header d-flex justify-content-between align-items-center">
            <h3 className="card-title mb-0">{t('media_library.list_title')}</h3>
            <div className="d-flex align-items-center gap-2">
              <span className="text-secondary small">{t('media_library.total_items', { count: pagination.total || 0 })}</span>
              <select
                className="form-select form-select-sm"
                style={{ width: 'auto' }}
                value={perPage}
                onChange={(e) => {
                  setPerPage(Number(e.target.value));
                  setPage(1);
                }}
              >
                {[12, 20, 40, 80].map((size) => (
                  <option key={size} value={size}>{t('media_library.per_page', { count: size })}</option>
                ))}
              </select>
            </div>
          </div>
          <div className="card-body">
            {error && <div className="alert alert-danger">{error}</div>}
            {loading ? (
              <div className="text-center py-5">
                <div className="spinner-border text-primary" role="status">
                  <span className="visually-hidden">{t('common.loading')}</span>
                </div>
              </div>
            ) : (
              <>
                {assets.length === 0 ? (
                  <div className="text-center text-secondary py-5">{t('media_library.no_assets')}</div>
                ) : (
                  <div className="row g-3">
                    {assets.map((asset) => {
                      const editable = editingValues[asset.id] || {};
                      const isImage = asset.mime_type?.startsWith('image/');
                      const url = asset.url || `${window.location.origin}/${asset.path}`;
                      return (
                        <div className="col-12 col-md-6" key={asset.id}>
                          <div className="card h-100 shadow-sm">
                            {isImage ? (
                              <div className="ratio ratio-4x3 bg-light">
                                <img src={url} alt={asset.alt_text || asset.original_name} className="object-fit-cover" />
                              </div>
                            ) : (
                              <div className="ratio ratio-4x3 bg-light d-flex flex-column align-items-center justify-content-center text-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" className="icon icon-tabler icon-tabler-file" width="32" height="32" viewBox="0 0 24 24" strokeWidth="1.5" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round">
                                  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                  <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                  <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                </svg>
                                <div className="small mt-2">{asset.mime_type || t('media_library.other_file')}</div>
                              </div>
                            )}
                            <div className="card-body">
                              <div className="d-flex justify-content-between mb-1">
                                <strong className="text-truncate">{asset.original_name}</strong>
                                {(() => {
                                  const statusMap = {
                                    active: { label: t('media_library.status_active'), class: 'bg-success text-white' },
                                    draft: { label: t('media_library.status_draft'), class: 'bg-warning text-white' },
                                    archived: { label: t('media_library.status_archived'), class: 'bg-secondary text-white' },
                                  };
                                  const status = asset.status || 'active';
                                  const statusInfo = statusMap[status] || { label: status, class: 'bg-secondary text-white' };
                                  return <span className={`badge ${statusInfo.class}`}>{statusInfo.label}</span>;
                                })()}
                              </div>
                              <div className="text-secondary small mb-2">
                                {formatBytes(asset.size_bytes)} · {asset.mime_type || '未知格式'}
                              </div>
                              <div className="mb-2">
                                <label className="form-label form-label-sm">{t('media_library.alt_text')}</label>
                                <input
                                  type="text"
                                  className="form-control form-control-sm"
                                  value={editable.alt_text || ''}
                                  onChange={(e) => handleFieldChange(asset.id, 'alt_text', e.target.value)}
                                />
                              </div>
                              <div className="mb-2">
                                <label className="form-label form-label-sm">{t('media_library.caption')}</label>
                                <textarea
                                  className="form-control form-control-sm"
                                  rows="2"
                                  value={editable.caption || ''}
                                  onChange={(e) => handleFieldChange(asset.id, 'caption', e.target.value)}
                                />
                              </div>
                              <div className="mb-3">
                                <label className="form-label form-label-sm">{t('common.status')}</label>
                                <select
                                  className="form-select form-select-sm"
                                  value={editable.status || 'active'}
                                  onChange={(e) => handleFieldChange(asset.id, 'status', e.target.value)}
                                >
                                  <option value="active">{t('media_library.status_active')}</option>
                                  <option value="draft">{t('media_library.status_draft')}</option>
                                  <option value="archived">{t('media_library.status_archived')}</option>
                                </select>
                              </div>
                              <div className="d-flex flex-wrap gap-2">
                                <button type="button" className="btn btn-primary btn-sm" onClick={() => saveMeta(asset.id)}>
                                  {t('common.save')}
                                </button>
                                <button type="button" className="btn btn-outline-secondary btn-sm" onClick={() => copyUrl(url)}>
                                  {t('media_library.copy_link')}
                                </button>
                                <button type="button" className="btn btn-warning btn-sm" onClick={() => toggleStatus(asset)}>
                                  {asset.status === 'active' ? t('media_library.status_archived') : t('media_library.status_active')}
                                </button>
                                <button
                                  type="button"
                                  className="btn btn-danger btn-sm"
                                  disabled={deletingId === asset.id}
                                  onClick={() => deleteAsset(asset)}
                                >
                                  {deletingId === asset.id ? t('common.loading') : t('common.delete')}
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
                {assets.length > 0 && (
                  <div className="d-flex justify-content-between align-items-center mt-4">
                    <span className="text-secondary small">
                       {t('common.pagination', { page, total: totalPages })}
                    </span>
                    <div className="btn-group">
                      <button
                        type="button"
                        className="btn btn-outline-secondary btn-sm"
                        disabled={page <= 1}
                        onClick={() => setPage((prev) => Math.max(1, prev - 1))}
                      >
                        {t('media_library.prev_page')}
                      </button>
                      <button
                        type="button"
                        className="btn btn-outline-secondary btn-sm"
                        disabled={page >= totalPages}
                        onClick={() => setPage((prev) => Math.min(totalPages, prev + 1))}
                      >
                        {t('media_library.next_page')}
                      </button>
                    </div>
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}

const mountNode = document.getElementById('media-library-app');
if (mountNode) {
  const apiBase = mountNode.dataset.apiBase;
  ReactDOM.createRoot(mountNode).render(
    <React.StrictMode>
      <MediaLibraryApp apiBase={apiBase} />
    </React.StrictMode>
  );
}
