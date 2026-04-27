import React, { useState, useEffect, useMemo, useRef } from 'react';
import ReactDOM from 'react-dom/client';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';

function ContentBlocks() {
  const [view, setView] = useState('list'); // list, edit
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(false);
  const [pagination, setPagination] = useState({ current_page: 1, total_pages: 1 });
  const [filters, setFilters] = useState({ keyword: '', group: '', type: '', status: '' });
  const [currentPage, setCurrentPage] = useState(1);
  const [groups, setGroups] = useState([]);
  
  // Edit Form State
  const [editId, setEditId] = useState(null);
  const [formData, setFormData] = useState({
    short_code: '',
    group_name: '',
    type: 'raw_text',
    content: '',
    page_link: '',
    status: 1
  });
  const quillRef = useRef(null);
  const [showHtmlSource, setShowHtmlSource] = useState(false);

  useEffect(() => {
    if (view === 'list') {
      fetchList();
    }
  }, [view, filters, currentPage]);

  const fetchList = async () => {
    setLoading(true);
    try {
      const params = new URLSearchParams({
        page: currentPage,
        ...filters
      });
      const res = await fetch(`/opanel/content-blocks/list?${params}`);
      const json = await res.json();
      if (json.success) {
        setItems(json.data);
        setGroups(json.groups || []);
        setPagination(json.pagination);
      }
    } catch (err) {
      console.error(err);
      window.toast && window.toast.error('載入失敗');
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({ ...prev, [key]: value }));
    setCurrentPage(1);
  };

  const handleCreate = () => {
    setEditId(null);
    setFormData({
      short_code: '',
      group_name: '',
      type: 'raw_text',
      content: '',
      page_link: '',
      status: 1
    });
    setView('edit');
  };

  const handleEdit = (item) => {
    setEditId(item.id);
    setFormData({
      short_code: item.short_code,
      group_name: item.group_name || '',
      type: item.type,
      content: item.content || '',
      page_link: item.page_link || '',
      status: item.status
    });
    setView('edit');
  };

  const handleDelete = async (id) => {
    if (!confirm('確定要刪除嗎？')) return;
    try {
      const res = await fetch(`/opanel/content-blocks/${id}/delete`, { method: 'POST' });
      const json = await res.json();
      if (json.success) {
        window.toast && window.toast.success('已刪除');
        fetchList();
      } else {
        window.toast && window.toast.error(json.message);
      }
    } catch (err) {
      window.toast && window.toast.error('刪除失敗');
    }
  };

  const handleToggleStatus = async (id) => {
    try {
      const res = await fetch(`/opanel/content-blocks/${id}/status`, { method: 'POST' });
      const json = await res.json();
      if (json.success) {
        setItems(prev => prev.map(item => item.id === id ? { ...item, status: json.new_status } : item));
      }
    } catch (err) {
      console.error(err);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    const url = editId ? `/opanel/content-blocks/${editId}` : '/opanel/content-blocks';
    try {
      const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });
      const json = await res.json();
      if (json.success) {
        window.toast && window.toast.success(editId ? '已更新' : '已新增');
        setView('list');
      } else {
        window.toast && window.toast.error(json.message || '儲存失敗');
      }
    } catch (err) {
      window.toast && window.toast.error('儲存錯誤');
    } finally {
      setLoading(false);
    }
  };

  const handleImageUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const data = new FormData();
    data.append('file', file);

    try {
      setLoading(true);
      const res = await fetch('/opanel/content-blocks/upload', { method: 'POST', body: data });
      const json = await res.json();
      if (json.success) {
        setFormData(prev => ({ ...prev, content: json.url }));
      } else {
        window.toast && window.toast.error(json.message || '上傳失敗');
      }
    } catch (err) {
      window.toast && window.toast.error('上傳錯誤');
    } finally {
      setLoading(false);
    }
  };

  const copyShortCode = (code) => {
      navigator.clipboard.writeText(`{{ short_code('${code}') }}`);
      window.toast && window.toast.success('複製成功');
  };

  // Image Handler for Quill
  const imageHandler = () => {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = async () => {
      const file = input.files[0];
      if (file) {
        const data = new FormData();
        data.append('file', file);

        try {
          const response = await fetch('/opanel/content-blocks/upload', {
            method: 'POST',
            body: data
          });
          const result = await response.json();
          if (result.success && result.url) {
            const quill = quillRef.current.getEditor();
            const range = quill.getSelection(true);
            quill.insertEmbed(range.index, 'image', result.url);
          } else {
            window.toast && window.toast.error('圖片上傳失敗: ' + (result.message || '未知錯誤'));
          }
        } catch (error) {
          console.error('Error uploading image:', error);
          window.toast && window.toast.error('圖片上傳錯誤');
        }
      }
    };
  };

  const quillModules = useMemo(() => ({
    toolbar: {
      container: [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike', 'blockquote'],
        [{'list': 'ordered'}, {'list': 'bullet'}],
        ['link', 'image'],
        ['clean']
      ],
      handlers: {
        image: imageHandler
      }
    }
  }), []);

  if (view === 'edit') {
    return (
      <div className="card">
        {/* ... headers ... */}
        <div className="card-header">
          <h3 className="card-title">{editId ? '編輯區塊' : '新增區塊'}</h3>
          <button className="btn btn-secondary ms-auto" onClick={() => setView('list')}>返回</button>
        </div>
        <div className="card-body">
          <form onSubmit={handleSubmit}>
            <div className="row">
              <div className="col-md-8">
                {/* ... existing fields ... */}
                
                <div className="mb-3">
                  <label className="form-label required">名稱短碼 (Short Code)</label>
                  <input type="text" className="form-control" value={formData.short_code} 
                    onChange={e => setFormData({...formData, short_code: e.target.value})} 
                    placeholder="例如: home_banner_text"
                    required />
                  <small className="text-muted">唯一識別碼，僅限英數字與底線</small>
                </div>

                <div className="mb-3">
                  <label className="form-label">連結頁面 (選填)</label>
                  <input type="text" className="form-control" value={formData.page_link} 
                    onChange={e => setFormData({...formData, page_link: e.target.value})} 
                    placeholder="識別用，例如: /about" />
                </div>

                <div className="mb-3">
                  <label className="form-label required">內容類型</label>
                  <select className="form-select" value={formData.type} 
                    onChange={e => setFormData({...formData, type: e.target.value})}>
                    <option value="raw_text">純文字 (Raw Text)</option>
                    <option value="html">HTML (Rich Editor)</option>
                    <option value="image">圖片 (Image)</option>
                    <option value="image_url">圖片網址 (Image URL)</option>
                  </select>
                </div>

                <div className="mb-3">
                  <label className="form-label required">內容</label>
                  
                  {formData.type === 'raw_text' && (
                    <textarea className="form-control" rows="10" 
                      value={formData.content} 
                      onChange={e => setFormData({...formData, content: e.target.value})}></textarea>
                  )}

                  {formData.type === 'html' && (
                    <div>
                      <div className="mb-2">
                        <button 
                          type="button"
                          className="btn btn-sm btn-outline-secondary"
                          onClick={() => setShowHtmlSource(!showHtmlSource)}
                        >
                          {showHtmlSource ? '📝 視覺編輯器' : '💻 HTML 原始碼'}
                        </button>
                      </div>
                      {showHtmlSource ? (
                        <textarea 
                          className="form-control font-monospace" 
                          rows="15"
                          value={formData.content}
                          onChange={e => setFormData({...formData, content: e.target.value})}
                          style={{ fontSize: '13px' }}
                        />
                      ) : (
                        <ReactQuill 
                          ref={quillRef}
                          theme="snow" 
                          value={formData.content} 
                          onChange={val => setFormData({...formData, content: val})} 
                          modules={quillModules}
                          style={{ height: '300px', marginBottom: '50px' }}
                        />
                      )}
                    </div>
                  )}

                  {(formData.type === 'image' || formData.type === 'image_url') && (
                    <div>
                      {formData.content && (
                        <div className="mb-2 p-2 border rounded">
                          <img src={formData.content} alt="Preview" style={{ maxWidth: '100%', maxHeight: '300px' }} />
                        </div>
                      )}
                      <input type="file" className="form-control" accept="image/*" onChange={handleImageUpload} />
                      <input type="text" className="form-control mt-2" placeholder="或直接輸入圖片網址"
                        value={formData.content}
                        onChange={e => setFormData({...formData, content: e.target.value})}
                      />
                      {formData.type === 'image_url' && (
                        <div className="form-text text-muted">此類型僅輸出純網址，用於 CSS background-image 或自訂 &lt;img src&gt;</div>
                      )}
                    </div>
                  )}
                </div>
              </div>
              
              <div className="col-md-4">
                 <div className="mb-3">
                    <label className="form-label">群組名稱</label>
                    <input type="text" className="form-control" list="group-list"
                        value={formData.group_name}
                        onChange={e => setFormData({...formData, group_name: e.target.value})}
                    />
                    <datalist id="group-list">
                        {groups.map(g => <option key={g} value={g} />)}
                    </datalist>
                 </div>

                 <div className="mb-3">
                    <label className="form-label">狀態</label>
                    <select className="form-select" value={formData.status}
                        onChange={e => setFormData({...formData, status: parseInt(e.target.value)})}>
                        <option value={1}>啟用</option>
                        <option value={0}>停用</option>
                    </select>
                 </div>

                 <button type="submit" className="btn btn-primary w-100" disabled={loading}>
                    {loading ? '儲存中...' : '儲存'}
                 </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    );
  }

  return (
    <div className="card">
      <div className="card-header">
        <h3 className="card-title">頁面區塊管理</h3>
        <div className="ms-auto d-flex gap-2">
            <select className="form-select w-auto" value={filters.group} onChange={e => handleFilterChange('group', e.target.value)}>
                <option value="">所有群組</option>
                {groups.map(g => <option key={g} value={g}>{g}</option>)}
            </select>
            <input type="text" className="form-control w-auto" placeholder="搜尋短碼..."
                value={filters.keyword} onChange={e => handleFilterChange('keyword', e.target.value)} />
            <button className="btn btn-primary" onClick={handleCreate}>新增區塊</button>
        </div>
      </div>
      <div className="table-responsive">
        <table className="table card-table table-vcenter text-nowrap datatable">
          <thead>
            <tr>
              <th>ID</th>
              <th>短碼 (Short Code)</th>
              <th>群組</th>
              <th>類型</th>
              <th>預覽/內容摘要</th>
              <th>狀態</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
                <tr><td colSpan="7" className="text-center">載入中...</td></tr>
            ) : items.length === 0 ? (
                <tr><td colSpan="7" className="text-center">尚無資料</td></tr>
            ) : items.map(item => (
              <tr key={item.id}>
                <td>{item.id}</td>
                <td>
                    <span className="fw-bold cursor-pointer" onClick={() => copyShortCode(item.short_code)} title="點擊複製代碼">
                        {item.short_code}
                    </span>
                    {item.page_link && <div className="text-muted small">{item.page_link}</div>}
                </td>
                <td>{item.group_name || '-'}</td>
                <td>
                    <span className={`badge text-white ${item.type === 'image' ? 'bg-indigo' : item.type === 'image_url' ? 'bg-purple' : item.type === 'html' ? 'bg-orange' : 'bg-secondary'}`}>
                        {item.type}
                    </span>
                </td>
                <td style={{ maxWidth: '300px', whiteSpace: 'normal' }}>
                    {(item.type === 'image' || item.type === 'image_url') ? (
                        <img src={item.content} alt="preview" style={{ height: '50px', objectFit: 'cover' }} />
                    ) : (
                        <div className="text-truncate">{item.content?.replace(/<[^>]+>/g, '').substring(0, 50)}</div>
                    )}
                </td>
                <td>
                  <label className="form-check form-switch m-0">
                    <input className="form-check-input" type="checkbox" 
                        checked={item.status == 1} 
                        onChange={() => handleToggleStatus(item.id)} />
                  </label>
                </td>
                <td>
                  <button className="btn btn-sm btn-outline-primary me-2" onClick={() => handleEdit(item)}>編輯</button>
                  <button className="btn btn-sm btn-outline-danger" onClick={() => handleDelete(item.id)}>刪除</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <div className="card-footer d-flex align-items-center">
        <p className="m-0 text-muted">
            共 {pagination.total_items} 筆
        </p>
        <ul className="pagination m-0 ms-auto">
            <li className={`page-item ${currentPage <= 1 ? 'disabled' : ''}`}>
                <button className="page-link" onClick={() => setCurrentPage(p => p - 1)}>上一頁</button>
            </li>
            <li className="page-item active">
                <span className="page-link">{currentPage} / {pagination.total_pages}</span>
            </li>
            <li className={`page-item ${currentPage >= pagination.total_pages ? 'disabled' : ''}`}>
                <button className="page-link" onClick={() => setCurrentPage(p => p + 1)}>下一頁</button>
            </li>
        </ul>
      </div>
    </div>
  );
}

export default ContentBlocks;

const mountNode = document.getElementById('content-blocks-app');
if (mountNode) {
    ReactDOM.createRoot(mountNode).render(
        <React.StrictMode>
            <ContentBlocks />
        </React.StrictMode>
    );
}
