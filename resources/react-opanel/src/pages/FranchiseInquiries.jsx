import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../i18n';

function FranchiseInquiriesApp() {
  const { t } = useTranslation();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [keywordFilter, setKeywordFilter] = useState('');
  const [pagination, setPagination] = useState({ current_page: 1, total_pages: 1 });

  const loadItems = async (page = 1) => {
    setLoading(true);
    try {
      let url = `/opanel/franchise-inquiries/list?page=${page}`;
      if (keywordFilter) {
        url += `&keyword=${encodeURIComponent(keywordFilter)}`;
      }
      
      const response = await fetch(url);
      const data = await response.json();
      
      if (data.success) {
        setItems(data.data || []);
        setPagination(data.pagination);
      }
    } catch (error) {
        console.error('Load failed', error);
        // window.showToast('Error loading data', 'error');
    } finally {
      setLoading(false);
    }
  };

  const toggleStatus = async (id, currentStatus) => {
      const newStatus = currentStatus === 1 ? 0 : 1;
      try {
          const formData = new URLSearchParams();
          formData.append('status', newStatus);

          const response = await fetch(`/opanel/franchise-inquiries/${id}/status`, {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: formData
          });
          const result = await response.json();
          if (result.success) {
              setItems(items.map(item => item.id === id ? { ...item, status: newStatus } : item));
              // window.showToast('Status updated', 'success');
          } else {
              // window.showToast('Update failed', 'error');
          }
      } catch (error) {
          console.error('Update failed', error);
      }
  };

  useEffect(() => {
    loadItems();
  }, []);

  return (
    <div className="container-fluid">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h1 className="h2 mb-0">加盟諮詢管理</h1>
      </div>

      <div className="card shadow mb-4">
        <div className="card-header py-3 d-flex justify-content-between align-items-center">
            <h3 className="m-0 font-weight-bold text-primary">諮詢列表</h3>
             <div className="d-flex">
                <input 
                  type="text" 
                  className="form-control mr-2" 
                  placeholder="搜尋姓名/Email/電話"
                  value={keywordFilter}
                  onChange={(e) => setKeywordFilter(e.target.value)}
                />
                <button 
                  className="btn btn-outline-primary ms-2"
                  onClick={() => loadItems(1)}
                >
                  搜尋
                </button>
              </div>
        </div>
        <div className="card-body">
            <div className="table-responsive">
                <table className="table table-bordered table-hover" width="100%" cellSpacing="0">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="15%">姓名</th>
                            <th width="15%">電話</th>
                            <th width="20%">Email</th>
                            <th>主旨/內容</th>
                            <th width="10%">日期</th>
                            <th width="10%">狀態</th>
                            <th width="10%">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        {loading ? (
                             <tr><td colSpan="8" className="text-center">Loading...</td></tr>
                        ) : items.length === 0 ? (
                             <tr><td colSpan="8" className="text-center">無資料</td></tr>
                        ) : (
                            items.map(item => (
                                <tr key={item.id}>
                                    <td>{item.id}</td>
                                    <td>{item.name}</td>
                                    <td>{item.phone}</td>
                                    <td>{item.email}</td>
                                    <td>
                                        <strong>{item.subject}</strong><br/>
                                        <small className="text-muted text-truncate d-inline-block" style={{maxWidth: '200px'}}>{item.message}</small>
                                    </td>
                                    <td>{item.created_at}</td>
                                    <td>
                                        {item.status == 1 ? (
                                            <span className="badge bg-success text-white">已聯絡</span>
                                        ) : (
                                            <span className="badge bg-warning text-white">未聯絡</span>
                                        )}
                                    </td>
                                    <td>
                                        <button 
                                            className={`btn btn-sm ${item.status == 1 ? 'btn-outline-secondary' : 'btn-outline-success'}`}
                                            onClick={() => toggleStatus(item.id, item.status)}
                                        >
                                            {item.status == 1 ? '標示未聯絡' : '標示已聯絡'}
                                        </button>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>

             {/* Pagination */}
             {pagination.total_pages > 1 && (
                <div className="d-flex justify-content-center mt-3">
                    <nav>
                        <ul className="pagination">
                            {[...Array(pagination.total_pages)].map((_, i) => (
                                <li key={i} className={`page-item ${pagination.current_page === i + 1 ? 'active' : ''}`}>
                                    <button className="page-link" onClick={() => loadItems(i + 1)}>
                                        {i + 1}
                                    </button>
                                </li>
                            ))}
                        </ul>
                    </nav>
                </div>
            )}
        </div>
      </div>
    </div>
  );
}

const root = ReactDOM.createRoot(document.getElementById('franchise-inquiries-app'));
root.render(
  <React.StrictMode>
    <FranchiseInquiriesApp />
  </React.StrictMode>
);
