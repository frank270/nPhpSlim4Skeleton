import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom/client';

function CmsCategoriesApp() {
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // 從 HTML 中獲取 API URL
  const apiUrl = document.getElementById('cms-categories-app').dataset.apiUrl;

  // 載入分類列表
  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const response = await fetch(`${apiUrl}`);
        const data = await response.json();

        if (!data.success) {
          throw new Error(data.message || '載入分類失敗');
        }

        setCategories(data.data || []);
      } catch (err) {
        setError('載入分類資料時發生錯誤');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchCategories();
  }, [apiUrl]);

  return (
    <div className="container-fluid">
      

      {error && <div className="alert alert-danger">{error}</div>}

      {loading ? (
        <div className="text-center">
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">載入中...</span>
          </div>
        </div>
      ) : (
        <table className="table table-bordered">
          <thead>
            <tr>
              <th>分類名稱</th>
              <th>標識</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            {categories.length === 0 ? (
              <tr>
                <td colSpan="3" className="text-center">尚未建立任何分類</td>
              </tr>
            ) : (
              categories.map((category) => (
                <tr key={category.id}>
                  <td>{category.name}</td>
                  <td>{category.slug}</td>
                  <td>
                    <button className="btn btn-sm btn-primary">編輯</button>
                    <button className="btn btn-sm btn-danger ms-2">刪除</button>
                  </td>
                </tr>
              ))
            )}
          </tbody>
        </table>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('cms-categories-app')).render(
  <React.StrictMode>
    <CmsCategoriesApp />
  </React.StrictMode>
);