import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../i18n';

const initialForm = {
  name: '',
  slug: '',
  description: '',
  sort_order: 0,
  is_active: 1,
};

const toast = (message, type = 'success') => {
    if (window.showToast) {
        window.showToast(message, type);
    } else if (window.Tabler?.Toast) {
        window.Tabler.Toast.show(message, { color: type === 'success' ? 'green' : type === 'error' ? 'red' : 'blue' });
    } else {
        alert(message);
    }
};

function MenuCategoriesApp({ apiBase }) {
  const { t } = useTranslation();
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(initialForm);
  const [editingId, setEditingId] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  const loadCategories = async () => {
    setLoading(true);
    try {
      const res = await fetch(`${apiBase}/list`);
      const data = await res.json();
      if (data.success) {
        setCategories(data.data);
      } else {
        toast(data.message || '載入失敗', 'error');
      }
    } catch (e) {
      toast(e.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadCategories();
  }, [apiBase]);

  const handleEdit = (category) => {
    setForm({
      name: category.name,
      slug: category.slug,
      description: category.description || '',
      sort_order: category.sort_order,
      is_active: category.is_active,
    });
    setEditingId(category.id);
    setShowForm(true);
  };

  const handleDelete = async (id) => {
    if (!confirm('確定要刪除嗎？')) return;
    try {
      const res = await fetch(`${apiBase}/${id}/delete`, { method: 'DELETE' });
      const data = await res.json();
      if (data.success) {
        toast('刪除成功');
        loadCategories();
      } else {
        toast(data.message || '刪除失敗', 'error');
      }
    } catch (e) {
      toast(e.message, 'error');
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    try {
      const url = editingId 
        ? `${apiBase}/${editingId}/edit`
        : `${apiBase}/create`;
      
      const formData = new URLSearchParams();
      for (const key in form) {
        formData.append(key, form[key]);
      }

      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString(),
      });
      const data = await res.json();
      
      if (data.success) {
        toast('儲存成功');
        setShowForm(false);
        setForm(initialForm);
        setEditingId(null);
        loadCategories();
      } else {
        toast(data.message || '儲存失敗', 'error');
      }
    } catch (e) {
      toast(e.message, 'error');
    } finally {
      setSubmitting(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setForm(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? (checked ? 1 : 0) : value
    }));
  };

  return (
    <div>
      <div className="d-flex justify-content-between mb-3">
        <h3>分類列表</h3>
        <button className="btn btn-primary" onClick={() => {
          setForm(initialForm);
          setEditingId(null);
          setShowForm(!showForm);
        }}>
          {showForm ? '取消' : '新增分類'}
        </button>
      </div>

      {showForm && (
        <div className="card mb-3">
          <div className="card-body">
            <form onSubmit={handleSubmit}>
              <div className="row g-3">
                <div className="col-md-6">
                  <label className="form-label">名稱 <span className="text-danger">*</span></label>
                  <input type="text" className="form-control" name="name" value={form.name} onChange={handleInputChange} required />
                </div>
                <div className="col-md-6">
                  <label className="form-label">Slug <span className="text-danger">*</span></label>
                  <input type="text" className="form-control" name="slug" value={form.slug} onChange={handleInputChange} required />
                </div>
                <div className="col-12">
                  <label className="form-label">描述</label>
                  <textarea className="form-control" name="description" value={form.description} onChange={handleInputChange} rows="2"></textarea>
                </div>
                <div className="col-md-6">
                  <label className="form-label">排序</label>
                  <input type="number" className="form-control" name="sort_order" value={form.sort_order} onChange={handleInputChange} />
                </div>
                <div className="col-md-6 d-flex align-items-end">
                  <label className="form-check form-switch">
                    <input className="form-check-input" type="checkbox" name="is_active" checked={form.is_active === 1} onChange={handleInputChange} />
                    <span className="form-check-label">啟用</span>
                  </label>
                </div>
                <div className="col-12">
                  <button type="submit" className="btn btn-primary" disabled={submitting}>
                    {submitting ? '處理中...' : '儲存'}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      )}

      <div className="card">
        <div className="table-responsive">
          <table className="table table-vcenter card-table">
            <thead>
              <tr>
                <th>名稱</th>
                <th>Slug</th>
                <th>排序</th>
                <th>狀態</th>
                <th className="w-1">操作</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="5" className="text-center">載入中...</td></tr>
              ) : categories.length === 0 ? (
                <tr><td colSpan="5" className="text-center">無資料</td></tr>
              ) : (
                categories.map(cat => (
                  <tr key={cat.id}>
                    <td>{cat.name}</td>
                    <td>{cat.slug}</td>
                    <td>{cat.sort_order}</td>
                    <td>
                      {cat.is_active === 1 
                        ? <span className="badge bg-success text-white">啟用</span> 
                        : <span className="badge bg-secondary">停用</span>}
                    </td>
                    <td>
                      <div className="btn-list flex-nowrap">
                        <button className="btn btn-white btn-sm" onClick={() => handleEdit(cat)}>編輯</button>
                        <button className="btn btn-danger btn-sm" onClick={() => handleDelete(cat.id)}>刪除</button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

const root = document.getElementById('menu-categories-app');
if (root) {
  const apiBase = root.dataset.apiBase;
  ReactDOM.createRoot(root).render(<MenuCategoriesApp apiBase={apiBase} />);
}
