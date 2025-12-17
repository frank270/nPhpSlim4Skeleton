import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';

function CmsList({ onCreate, onEdit }) {
  const { t } = useTranslation();
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    keyword: '',
    status: '',
    type: ''
  });
  const [pagination, setPagination] = useState({
    current_page: 1,
    total_pages: 1,
    total_items: 0
  });

  const fetchPosts = async (page = 1) => {
    setLoading(true);
    try {
      const query = new URLSearchParams({
        page,
        ...filters
      });
      const response = await fetch(`/opanel/cms/posts/list?${query}`);
      const data = await response.json();
      if (data.success) {
        setPosts(data.data);
        setPagination(data.pagination);
      }
    } catch (error) {
      console.error('Error fetching posts:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPosts();
  }, [filters.status, filters.type]); // Auto-refresh on filter change

  const handleSearch = (e) => {
    e.preventDefault();
    fetchPosts(1);
  };

  const handlePageChange = (newPage) => {
    if (newPage >= 1 && newPage <= pagination.total_pages) {
      fetchPosts(newPage);
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm(t('common.confirm_delete'))) return;

    try {
      const response = await fetch(`/opanel/cms/posts/${id}/delete`, {
        method: 'DELETE'
      });
      const data = await response.json();
      if (data.success) {
        fetchPosts(pagination.current_page);
      } else {
        alert(data.message || 'Error deleting post');
      }
    } catch (error) {
      console.error('Error deleting post:', error);
    }
  };

  return (
    <div className="card">
        <div className="card-header">
            <h3 className="card-title">文章管理</h3>
            <div className="card-actions">
                <button className="btn btn-primary" onClick={onCreate}>
                    <svg xmlns="http://www.w3.org/2000/svg" className="icon" width="24" height="24" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                    新增文章
                </button>
            </div>
        </div>
        <div className="card-body border-bottom py-3">
            <div className="d-flex">
                <div className="text-muted">
                    <select className="form-select form-select-sm d-inline-block w-auto" 
                        value={filters.type} 
                        onChange={(e) => setFilters({...filters, type: e.target.value})}
                    >
                        <option value="">所有類型</option>
                        <option value="news">最新消息</option>
                        <option value="article">專欄文章</option>
                        <option value="statics">靜態頁面</option>
                    </select>
                </div>
                <div className="text-muted ms-2">
                    <select className="form-select form-select-sm d-inline-block w-auto" 
                        value={filters.status} 
                        onChange={(e) => setFilters({...filters, status: e.target.value})}
                    >
                        <option value="">所有狀態</option>
                        <option value="published">已發布</option>
                        <option value="draft">草稿</option>
                    </select>
                </div>
                <div className="ms-auto text-muted">
                    <div className="d-inline-block">
                        <input type="text" className="form-control form-control-sm" 
                            placeholder="搜尋標題..." 
                            value={filters.keyword}
                            onChange={(e) => setFilters({...filters, keyword: e.target.value})}
                            onKeyDown={(e) => e.key === 'Enter' && handleSearch(e)}
                        />
                    </div>
                </div>
            </div>
        </div>
        <div className="table-responsive">
            <table className="table card-table table-vcenter text-nowrap datatable">
                <thead>
                    <tr>
                        <th className="w-1">ID</th>
                        <th>標題</th>
                        <th>類型</th>
                        <th>狀態</th>
                        <th>發布時間</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {loading ? (
                        <tr><td colSpan="6" className="text-center py-4">載入中...</td></tr>
                    ) : posts.length === 0 ? (
                        <tr><td colSpan="6" className="text-center py-4">尚無資料</td></tr>
                    ) : (
                        posts.map(post => (
                            <tr key={post.id}>
                                <td>{post.id}</td>
                                <td>
                                    {post.cover_image && (
                                        <img src={post.cover_image} alt="" className="avatar avatar-sm me-2 rounded" />
                                    )}
                                    {post.title}
                                    <div className="text-muted small">{post.slug}</div>
                                </td>
                                <td>
                                    <span className={`badge bg-${post.type === 'news' ? 'blue' : (post.type === 'article' ? 'green' : 'orange')}-lt`}>
                                        {post.type}
                                    </span>
                                </td>
                                <td>
                                    {post.status === 'published' ? (
                                        <span className="badge bg-success me-1"></span>
                                    ) : (
                                        <span className="badge bg-secondary me-1"></span>
                                    )}
                                    {post.status}
                                </td>
                                <td>{post.published_at || '-'}</td>
                                <td className="text-end">
                                    <button className="btn btn-sm btn-white me-2" onClick={() => onEdit(post.id)}>編輯</button>
                                    <button className="btn btn-sm btn-danger btn-icon" onClick={() => handleDelete(post.id)}>
                                        <svg xmlns="http://www.w3.org/2000/svg" className="icon" width="24" height="24" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    </button>
                                </td>
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
        <div className="card-footer d-flex align-items-center">
            <p className="m-0 text-muted">
                顯示 {(pagination.current_page - 1) * 20 + 1} 到 {Math.min(pagination.current_page * 20, pagination.total_items)} 筆，共 {pagination.total_items} 筆
            </p>
            <ul className="pagination m-0 ms-auto">
                <li className={`page-item ${pagination.current_page === 1 ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => handlePageChange(pagination.current_page - 1)}>
                        <svg xmlns="http://www.w3.org/2000/svg" className="icon" width="24" height="24" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="15 6 9 12 15 18" /></svg>
                        上一頁
                    </button>
                </li>
                <li className={`page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}`}>
                    <button className="page-link" onClick={() => handlePageChange(pagination.current_page + 1)}>
                        下一頁
                        <svg xmlns="http://www.w3.org/2000/svg" className="icon" width="24" height="24" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor" fill="none" strokeLinecap="round" strokeLinejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="9 6 15 12 9 18" /></svg>
                    </button>
                </li>
            </ul>
        </div>
    </div>
  );
}

export default CmsList;
