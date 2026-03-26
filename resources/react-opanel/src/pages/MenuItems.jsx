import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../i18n';

const initialForm = {
  item_code: '',
  name: '',
  description: '',
  category_id: '',
  price_original: '',
  price_sale: '',
  image_path: '',
  tags: '',
  is_best_seller: 0,
  status: 'draft',
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

// Custom Tags Input Component
const TagsInput = ({ value, onChange, placeholder }) => {
  const [inputValue, setInputValue] = useState('');

  const handleKeyDown = (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const val = inputValue.trim();
      if (val && !value.includes(val)) {
        onChange([...value, val]);
        setInputValue('');
      }
    } else if (e.key === 'Backspace' && !inputValue && value.length > 0) {
      onChange(value.slice(0, -1));
    }
  };

  const removeTag = (index) => {
    onChange(value.filter((_, i) => i !== index));
  };

  return (
    <div className="form-control d-flex flex-wrap gap-2 align-items-center" onClick={() => document.getElementById('tags-input-field')?.focus()}>
      {value.map((tag, index) => (
        <span key={index} className="badge bg-secondary text-white d-flex align-items-center gap-1">
          {tag}
          <span 
            className="cursor-pointer text-white opacity-75 hover-opacity-100" 
            style={{ cursor: 'pointer' }}
            onClick={(e) => { e.stopPropagation(); removeTag(index); }}
          >×</span>
        </span>
      ))}
      <input
        id="tags-input-field"
        type="text"
        className="border-0 p-0"
        style={{ outline: 'none', minWidth: '100px', flex: 1 }}
        value={inputValue}
        onChange={(e) => setInputValue(e.target.value)}
        onKeyDown={handleKeyDown}
        placeholder={value.length === 0 ? placeholder : ''}
      />
    </div>
  );
};

const STATUS_MAP = {
  draft: '草稿',
  published: '已發布',
  archived: '已封存'
};

function MenuItemsApp({ apiBase, apiCategoryBase }) {

  const { t } = useTranslation();
  const [items, setItems] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(initialForm);
  const [editingId, setEditingId] = useState(null);
  const [submitting, setSubmitting] = useState(false);
  const [uploadProgress, setUploadProgress] = useState(null);
  const [uploading, setUploading] = useState(false);
  
  // Filters
  const [filterCategory, setFilterCategory] = useState('');

  const loadItems = async () => {
    setLoading(true);
    try {
      let url = `${apiBase}/list?`;
      if (filterCategory) url += `category_id=${filterCategory}&`;
      
      const res = await fetch(url);
      const data = await res.json();
      if (data.success) {
        setItems(data.data);
      } else {
        toast(data.message || '載入失敗', 'error');
      }
    } catch (e) {
      toast(e.message, 'error');
    } finally {
      setLoading(false);
    }
  };

  const loadCategories = async () => {
    try {
      const res = await fetch(`${apiCategoryBase}/all`);
      const data = await res.json();
      if (data.success) {
        setCategories(data.data);
      }
    } catch (e) {
      console.error(e);
    }
  };

  useEffect(() => {
    loadCategories();
  }, [apiCategoryBase]);

  useEffect(() => {
    loadItems();
  }, [apiBase, filterCategory]);

  const handleEdit = (item) => {
    setForm({
      item_code: item.item_code || '',
      name: item.name,
      description: item.description || '',
      category_id: item.category_id,
      price_original: Math.round(item.price_original),
      price_sale: item.price_sale ? Math.round(item.price_sale) : '',
      image_path: item.image_path || '',
      tags: item.tags || '',
      is_best_seller: item.is_best_seller,
      status: item.status,
    });
    setEditingId(item.id);
    setShowForm(true);
  };

  const handleDelete = async (id) => {
    if (!confirm('確定要刪除嗎？')) return;
    try {
      const res = await fetch(`${apiBase}/${id}/delete`, { method: 'DELETE' });
      const data = await res.json();
      if (data.success) {
        toast('刪除成功');
        loadItems();
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
      
      // Prepare payload
      const payload = { ...form };
      if (!payload.price_sale) payload.price_sale = ''; // Send empty string if null
      if (!payload.image_path) payload.image_path = '';

      const formData = new URLSearchParams();
      for (const key in payload) {
          formData.append(key, payload[key]);
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
        loadItems();
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

  const handleBatchCsv = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    setLoading(true);
    const formData = new FormData();
    formData.append('file', file);
    try {
      const res = await fetch('/opanel/menu/batch/csv', { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        toast(`CSV 匯入成功：新增 ${data.data.added} 筆，更新 ${data.data.updated} 筆`);
        if (data.data.errors && data.data.errors.length > 0) {
           console.warn('匯入有部分錯誤', data.data.errors);
           toast(`有 ${data.data.errors.length} 筆發生錯誤，請查看 Console`, 'error');
        }
        loadItems();
      } else {
        toast(data.message || 'CSV 匯入失敗', 'error');
      }
    } catch (err) {
      toast(err.message, 'error');
    } finally {
      setLoading(false);
      e.target.value = ''; // reset
    }
  };

  const handleBatchZip = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    setLoading(true);
    const formData = new FormData();
    formData.append('file', file);
    try {
      const res = await fetch('/opanel/menu/batch/zip', { method: 'POST', body: formData });
      const data = await res.json();
      if (data.success) {
        toast(`ZIP 匯入成功：配對 ${data.data.matched} 張圖片`);
        if (data.data.unmatched && data.data.unmatched.length > 0) {
           console.warn('未對應的圖片', data.data.unmatched);
           toast(`有 ${data.data.unmatched.length} 張圖無法對應，請查看 Console`, 'error');
        }
        loadItems();
      } else {
        toast(data.message || 'ZIP 匯入失敗', 'error');
      }
    } catch (err) {
      toast(err.message, 'error');
    } finally {
      setLoading(false);
      e.target.value = ''; // reset
    }
  };

  return (
    <div>
      <div className="d-flex justify-content-between mb-3 flex-wrap gap-2">
        <div className="d-flex gap-2 align-items-center">
           <h3 className="m-0">商品列表</h3>
           <select className="form-select w-auto" value={filterCategory} onChange={(e) => setFilterCategory(e.target.value)}>
             <option value="">所有分類</option>
             {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
           </select>
        </div>
        <div className="d-flex gap-2 flex-wrap">
           <a href="/opanel/menu/batch/template" className="btn btn-outline-secondary" target="_blank" rel="noreferrer">
             下載 CSV 範本
           </a>
           <label className="btn btn-outline-primary mb-0">
             匯入 CSV
             <input type="file" hidden accept=".csv" onChange={handleBatchCsv} />
           </label>
           <label className="btn btn-outline-success mb-0">
             匯入圖檔 ZIP
             <input type="file" hidden accept=".zip" onChange={handleBatchZip} />
           </label>
           <button className="btn btn-primary" onClick={() => {
             setForm(initialForm);
             setEditingId(null);
             setShowForm(!showForm);
           }}>
             {showForm ? '取消' : '新增商品'}
           </button>
        </div>
      </div>

      {showForm && (
        <div className="card mb-3">
          <div className="card-body">
            <form onSubmit={handleSubmit}>
              <div className="row g-3">
                <div className="col-md-3">
                  <label className="form-label">菜單編號 (可選)</label>
                  <input type="text" className="form-control" name="item_code" value={form.item_code} onChange={handleInputChange} />
                </div>
                <div className="col-md-5">
                  <label className="form-label">名稱 <span className="text-danger">*</span></label>
                  <input type="text" className="form-control" name="name" value={form.name} onChange={handleInputChange} required />
                </div>
                <div className="col-md-4">
                  <label className="form-label">分類 <span className="text-danger">*</span></label>
                  <select className="form-select" name="category_id" value={form.category_id} onChange={handleInputChange} required>
                    <option value="">請選擇</option>
                     {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                  </select>
                </div>
                <div className="col-12">
                  <label className="form-label">描述</label>
                  <textarea className="form-control" name="description" value={form.description} onChange={handleInputChange} rows="2"></textarea>
                </div>
                 <div className="col-md-4">
                  <label className="form-label">原價 <span className="text-danger">*</span></label>
                  <input type="number" step="1" className="form-control" name="price_original" value={form.price_original} onChange={handleInputChange} required />
                </div>
                 <div className="col-md-4">
                  <label className="form-label">特價</label>
                  <input type="number" step="1" className="form-control" name="price_sale" value={form.price_sale} onChange={handleInputChange} />
                </div>
                 <div className="col-md-4">
                   <label className="form-label">狀態</label>
                   <select className="form-select" name="status" value={form.status} onChange={handleInputChange}>
                     <option value="draft">草稿</option>
                     <option value="published">已發布</option>
                     <option value="archived">已封存</option>
                   </select>
                </div>
                   <div className="col-md-6">
                   <label className="form-label">標籤</label>
                   <TagsInput 
                      value={form.tags ? form.tags.split(',').filter(t => t) : []} 
                      onChange={(tags) => setForm(prev => ({ ...prev, tags: tags.join(',') }))}
                      placeholder='輸入後按 Enter'
                   />
                </div>
                <div className="col-12">
                   <label className="form-label">商品圖片</label>
                   <div className="d-flex align-items-start gap-4">
                      {form.image_path && (
                        <div className="position-relative">
                          <img 
                            src={form.image_path.startsWith('http') ? form.image_path : `${window.location.origin}/${form.image_path}`} 
                            alt="Preview" 
                            className="img-thumbnail"
                            style={{ width: '150px', height: '150px', objectFit: 'cover' }}
                          />
                          <button 
                            type="button" 
                            className="btn-close position-absolute top-0 end-0 bg-white" 
                            aria-label="Remove"
                            onClick={() => setForm(prev => ({ ...prev, image_path: '' }))}
                          ></button>
                        </div>
                      )}
                      
                      <div className="flex-fill">
                         <input 
                            type="file" 
                            id="menu-item-image-upload"
                            className="form-control" 
                            accept="image/*"
                            disabled={uploading}
                            onChange={(e) => {
                              const file = e.target.files[0];
                              if (!file) return;

                              setUploading(true);
                              setUploadProgress(0);
                              const formData = new FormData();
                              formData.append('file', file);
                              formData.append('uploaded_from', 'menu-items');

                              const xhr = new XMLHttpRequest();
                              xhr.open('POST', '/opanel/media-assets/upload');
                              
                              xhr.upload.onprogress = (event) => {
                                if (event.lengthComputable) {
                                  const percent = Math.round((event.loaded / event.total) * 100);
                                  setUploadProgress(percent);
                                }
                              };

                              xhr.onload = () => {
                                setUploadProgress(100);
                                // Artificial delay to let user see 100%
                                setTimeout(() => {
                                    try {
                                      const data = JSON.parse(xhr.responseText);
                                      if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                                         setForm(prev => ({ ...prev, image_path: data.data.path }));
                                         toast('圖片上傳成功');
                                      } else {
                                         toast(data.message || '上傳失敗', 'error');
                                      }
                                    } catch (err) {
                                      toast('上傳回應解析錯誤', 'error');
                                    } finally {
                                      setUploadProgress(null);
                                      setUploading(false);
                                      // Clear input
                                      const input = document.getElementById('menu-item-image-upload');
                                      if (input) input.value = '';
                                    }
                                }, 500); 
                              };

                              xhr.onerror = () => {
                                toast('上傳發生網路錯誤', 'error');
                                setUploadProgress(null);
                                setUploading(false);
                              };

                              xhr.send(formData);
                            }}
                         />
                         <small className="form-text text-muted">支援 jpg, png, webp 格式</small>
                         {uploadProgress !== null && (
                            <div className="progress mt-2" style={{ height: '5px' }}>
                              <div className="progress-bar" style={{ width: `${uploadProgress}%` }}></div>
                            </div>
                         )}
                      </div>
                   </div>
                </div>
                <div className="col-md-6 d-flex align-items-end">
                  <label className="form-check form-switch">
                    <input className="form-check-input" type="checkbox" name="is_best_seller" checked={form.is_best_seller === 1} onChange={handleInputChange} />
                    <span className="form-check-label">設為熱銷</span>
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
                <th>分類</th>
                <th>價格</th>
                <th>狀態</th>
                <th>標籤</th>
                <th className="w-1">操作</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr><td colSpan="6" className="text-center">載入中...</td></tr>
              ) : items.length === 0 ? (
                <tr><td colSpan="6" className="text-center">無資料</td></tr>
              ) : (
                items.map(item => (
                  <tr key={item.id}>
                    <td>
                      {item.is_best_seller === 1 && <span className="badge bg-yellow me-1">熱銷</span>}
                      {item.item_code && <span className="text-muted pe-1">[{item.item_code}]</span>}
                      {item.name}
                    </td>
                    <td>{item.category_name}</td>
                    <td>
                      {item.price_sale ? (
                        <span>
                          <s className="text-muted">{Math.round(item.price_original)}</s> <b className="text-danger">{Math.round(item.price_sale)}</b>
                        </span>
                      ) : (
                        Math.round(item.price_original)
                      )}
                    </td>
                    <td>
                      <span className={`badge text-white bg-${item.status === 'published' ? 'success' : 'secondary'}`}>
                        {STATUS_MAP[item.status] || item.status}
                      </span>
                    </td>
                     <td>{item.tags}</td>
                    <td>
                      <div className="btn-list flex-nowrap">
                        <button className="btn btn-white btn-sm" onClick={() => handleEdit(item)}>編輯</button>
                        <button className="btn btn-danger btn-sm" onClick={() => handleDelete(item.id)}>刪除</button>
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

const root = document.getElementById('menu-items-app');
if (root) {
  const apiBase = root.dataset.apiBase;
  const apiCategoryBase = root.dataset.apiCategoryBase;
  ReactDOM.createRoot(root).render(<MenuItemsApp apiBase={apiBase} apiCategoryBase={apiCategoryBase} />);
}
