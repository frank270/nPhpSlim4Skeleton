import React, { useEffect, useMemo, useState } from 'react';
import ReactDOM from 'react-dom/client';

const initialFormState = {
  name: '',
  url: '',
  type: '',
  locale: '',
  description: '',
  opened_in_new_tab: true,
  is_active: true,
};

const generateUuid = () => {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID();
  }
  // Fallback：簡易 UUID 產生器
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
    const r = (Math.random() * 16) | 0;
    const v = c === 'x' ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
};

const toast = (message, type = 'success') => {
  if (window.showToast) {
    window.showToast(message, type);
  } else if (window.Tabler?.Toast) {
    window.Tabler.Toast.show(message, { color: type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue' });
  } else {
    // eslint-disable-next-line no-alert
    alert(message);
  }
};

function ExternalLinksApp({ apiBase }) {
  const [links, setLinks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [refreshKey, setRefreshKey] = useState(0);

  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(initialFormState);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const [isDeleting, setIsDeleting] = useState(false);
  const [targetLink, setTargetLink] = useState(null);

  const listEndpoint = useMemo(() => `${apiBase}/list`, [apiBase]);

  const loadLinks = async () => {
    setLoading(true);
    setError('');
    try {
      const response = await fetch(listEndpoint);
      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || '載入外部連結失敗');
      }
      setLinks(result.data || []);
    } catch (err) {
      setError(err.message);
      setLinks([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadLinks();
  }, [listEndpoint, refreshKey]);

  const handleInputChange = (event) => {
    const { name, value, type, checked } = event.target;
    if (type === 'checkbox') {
      setForm((prev) => ({ ...prev, [name]: checked }));
    } else {
      setForm((prev) => ({ ...prev, [name]: value }));
    }
  };

  const resetForm = () => {
    setForm(initialFormState);
    setShowForm(false);
  };

  const handleCreate = async (event) => {
    event.preventDefault();
    setIsSubmitting(true);
    try {
      if (!form.name.trim()) {
        throw new Error('請輸入名稱');
      }
      if (!form.url.trim()) {
        throw new Error('請輸入 URL');
      }

      const payload = {
        uuid: generateUuid(),
        name: form.name.trim(),
        url: form.url.trim(),
        type: form.type.trim() || 'default',
        locale: form.locale.trim() || null,
        description: form.description.trim() || null,
        opened_in_new_tab: form.opened_in_new_tab ? 1 : 0,
        is_active: form.is_active ? 1 : 0,
      };

      const response = await fetch(`${apiBase}/create`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });

      let result;
      if (response.ok) {
        result = await response.json();
      } else {
        const text = await response.text();
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || '建立外部連結失敗');
        } catch {
          throw new Error(text || '建立外部連結失敗');
        }
      }

      if (!result.success) {
        throw new Error(result.message || '建立外部連結失敗');
      }

      toast('外部連結已建立', 'success');
      resetForm();
      setRefreshKey((prev) => prev + 1);
    } catch (err) {
      toast(err.message || '建立外部連結失敗', 'error');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleToggleActive = async (link) => {
    const nextState = link.is_active ? 0 : 1;
    try {
      const response = await fetch(`${apiBase}/${link.id}/toggle-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ is_active: nextState }),
      });

      let result;
      if (response.ok) {
        result = await response.json();
      } else {
        const text = await response.text();
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || '更新狀態失敗');
        } catch {
          throw new Error(text || '更新狀態失敗');
        }
      }

      if (!result.success) {
        throw new Error(result.message || '更新狀態失敗');
      }

      toast('狀態已更新', 'success');
      setLinks((prev) => prev.map((item) => (item.id === link.id ? { ...item, is_active: nextState } : item)));
    } catch (err) {
      toast(err.message || '更新狀態失敗', 'error');
    }
  };

  const handleDelete = async (link) => {
    if (!window.confirm(`確定要刪除「${link.name}」嗎？`)) {
      return;
    }
    setTargetLink(link);
    setIsDeleting(true);
    try {
      const response = await fetch(`${apiBase}/${link.id}/delete`, {
        method: 'DELETE',
      });

      let result;
      if (response.ok) {
        result = await response.json();
      } else {
        const text = await response.text();
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || '刪除失敗');
        } catch {
          throw new Error(text || '刪除失敗');
        }
      }

      if (!result.success) {
        throw new Error(result.message || '刪除失敗');
      }

      toast('外部連結已刪除', 'success');
      setLinks((prev) => prev.filter((item) => item.id !== link.id));
    } catch (err) {
      toast(err.message || '刪除失敗', 'error');
    } finally {
      setIsDeleting(false);
      setTargetLink(null);
    }
  };

  const renderToolbar = () => (
    <div className="d-flex justify-content-between align-items-center mb-3">
      <h3 className="mb-0">外部連結列表</h3>
      <div className="d-flex gap-2">
        <button
          className="btn btn-outline-secondary"
          type="button"
          onClick={() => setRefreshKey((prev) => prev + 1)}
          disabled={loading}
        >
          <span className="me-1" aria-hidden="true">⟳</span>
          重新整理
        </button>
        <button
          className="btn btn-primary"
          type="button"
          onClick={() => setShowForm((prev) => !prev)}
        >
          {showForm ? '取消新增' : '新增外部連結'}
        </button>
      </div>
    </div>
  );

  const renderForm = () => {
    if (!showForm) return null;

    return (
      <div className="card mb-3">
        <div className="card-body">
          <form onSubmit={handleCreate}>
            <div className="row g-3">
              <div className="col-md-6">
                <label className="form-label">名稱<span className="text-danger">*</span></label>
                <input
                  type="text"
                  className="form-control"
                  name="name"
                  value={form.name}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  required
                />
              </div>
              <div className="col-md-6">
                <label className="form-label">URL<span className="text-danger">*</span></label>
                <input
                  type="url"
                  className="form-control"
                  name="url"
                  value={form.url}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder="https://example.com"
                  required
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">類型</label>
                <input
                  type="text"
                  className="form-control"
                  name="type"
                  value={form.type}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder="例如：cta、order、map"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">語系</label>
                <input
                  type="text"
                  className="form-control"
                  name="locale"
                  value={form.locale}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                  placeholder="例如：zh-TW"
                />
              </div>
              <div className="col-md-4">
                <label className="form-label">開啟方式</label>
                <div className="form-check form-switch mt-2">
                  <input
                    className="form-check-input"
                    type="checkbox"
                    id="opened_in_new_tab"
                    name="opened_in_new_tab"
                    checked={form.opened_in_new_tab}
                    onChange={handleInputChange}
                    disabled={isSubmitting}
                  />
                  <label className="form-check-label" htmlFor="opened_in_new_tab">
                    新分頁開啟
                  </label>
                </div>
              </div>
              <div className="col-12">
                <label className="form-label">描述</label>
                <textarea
                  className="form-control"
                  rows="2"
                  name="description"
                  value={form.description}
                  onChange={handleInputChange}
                  disabled={isSubmitting}
                ></textarea>
              </div>
              <div className="col-12">
                <div className="form-check">
                  <input
                    className="form-check-input"
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    checked={form.is_active}
                    onChange={handleInputChange}
                    disabled={isSubmitting}
                  />
                  <label className="form-check-label" htmlFor="is_active">
                    啟用此連結
                  </label>
                </div>
              </div>
            </div>
            <div className="mt-4 d-flex gap-2">
              <button type="submit" className="btn btn-primary" disabled={isSubmitting}>
                {isSubmitting ? '儲存中…' : '建立外部連結'}
              </button>
              <button type="button" className="btn btn-outline-secondary" onClick={resetForm} disabled={isSubmitting}>
                取消
              </button>
            </div>
          </form>
        </div>
      </div>
    );
  };

  const renderTable = () => {
    if (loading) {
      return (
        <div className="text-center py-5">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">載入中...</span>
          </div>
        </div>
      );
    }

    if (error) {
      return <div className="alert alert-danger">{error}</div>;
    }

    if (!links.length) {
      return <div className="alert alert-info">目前尚未建立任何外部連結。</div>;
    }

    return (
      <div className="table-responsive">
        <table className="table table-hover">
          <thead>
            <tr>
              <th>名稱</th>
              <th>URL</th>
              <th>類型</th>
              <th>語系</th>
              <th>狀態</th>
              <th className="text-end">操作</th>
            </tr>
          </thead>
          <tbody>
            {links.map((link) => (
              <tr key={link.id}>
                <td>{link.name}</td>
                <td>
                  <a href={link.url} target="_blank" rel="noreferrer">
                    {link.url}
                  </a>
                </td>
                <td>{link.type || '-'}</td>
                <td>{link.locale || '-'}</td>
                <td>
                  {link.is_active ? (
                    <span className="badge bg-success">啟用</span>
                  ) : (
                    <span className="badge bg-secondary">停用</span>
                  )}
                </td>
                <td className="text-end">
                  <div className="btn-group">
                    <button
                      type="button"
                      className="btn btn-sm btn-ghost-primary"
                      onClick={() => handleToggleActive(link)}
                    >
                      {link.is_active ? '停用' : '啟用'}
                    </button>
                    <button
                      type="button"
                      className="btn btn-sm btn-ghost-danger"
                      onClick={() => handleDelete(link)}
                      disabled={isDeleting && targetLink?.id === link.id}
                    >
                      刪除
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    );
  };

  return (
    <div className="container-xl mt-3">
      {renderToolbar()}
      {renderForm()}
      <div className="card">
        <div className="card-body">
          {renderTable()}
        </div>
      </div>
    </div>
  );
}

const mountNode = document.getElementById('external-links-app');

if (mountNode) {
  const apiBase = mountNode.dataset.apiBase || '/opanel/external-links';
  ReactDOM.createRoot(mountNode).render(
    <React.StrictMode>
      <ExternalLinksApp apiBase={apiBase} />
    </React.StrictMode>
  );
}
