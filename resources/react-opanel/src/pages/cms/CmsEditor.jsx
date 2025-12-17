import React, { useState, useEffect, useRef } from 'react';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';

function CmsEditor({ id, onBack }) {
  const isEdit = !!id;
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    title: '',
    slug: '',
    cover_image: '',
    content: '',
    tags: '',
    type: 'news',
    status: 'draft',
    sort_order: 0,
    published_at: ''
  });

  const quillRef = useRef(null);

  useEffect(() => {
    if (isEdit) {
      fetchPost();
    }
  }, [id]);

  const fetchPost = async () => {
    setLoading(true);
    try {
      const response = await fetch(`/opanel/cms/posts/${id}`);
      const data = await response.json();
      if (data.success) {
        setFormData(data.data);
      } else {
        window.toast.error(data.message);
        onBack();
      }
    } catch (error) {
      console.error('Error fetching post:', error);
      onBack();
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleEditorChange = (content) => {
    setFormData(prev => ({ ...prev, content }));
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
        data.append('uploaded_from', 'cms_quill');

        try {
          const response = await fetch('/opanel/media-assets/upload', {
            method: 'POST',
            body: data
          });
          const result = await response.json();
          if (result.success && result.data.url) {
            const quill = quillRef.current.getEditor();
            const range = quill.getSelection(true);
            quill.insertEmbed(range.index, 'image', result.data.url);
          } else {
            window.toast.error('Image upload failed: ' + (result.message || 'Unknown error'));
          }
        } catch (error) {
          console.error('Error uploading image:', error);
          window.toast.error('Error uploading image');
        }
      }
    };
  };

  // Cover Image Uploader
  const handleCoverUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const data = new FormData();
    data.append('file', file);
    data.append('uploaded_from', 'cms_cover');

    try {
      setLoading(true);
      const response = await fetch('/opanel/media-assets/upload', {
        method: 'POST',
        body: data
      });
      const result = await response.json();
      if (result.success && result.data.url) {
        setFormData(prev => ({ ...prev, cover_image: result.data.url }));
      } else {
        window.toast.error('Cover upload failed');
      }
    } catch (error) {
      console.error('Error uploading cover:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    const url = isEdit ? `/opanel/cms/posts/${id}/edit` : '/opanel/cms/posts/create';
    const method = 'POST';

    try {
      const params = new URLSearchParams();
      // Only append fields that have values or are explicitly needed
      for (const [key, value] of Object.entries(formData)) {
        if (value !== null && value !== undefined) {
             // Special handling for published_at: skip if empty string
             if (key === 'published_at' && value === '') continue;
             
             params.append(key, value);
        }
      }

      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params.toString()
      });
      const result = await response.json();

      if (result.success) {
        window.toast.success(isEdit ? '已更新' : '已新增');
        onBack();
      } else {
        window.toast.error(result.message || '儲存失敗');
      }
    } catch (error) {
      console.error('Error saving post:', error);
      window.toast.error('儲存失敗');
    } finally {
      setLoading(false);
    }
  };

  const modules = React.useMemo(() => ({
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

  if (loading && isEdit && !formData.title) return <div>載入中...</div>;

  return (
    <div className="card">
      <div className="card-header">
        <h3 className="card-title">{isEdit ? '編輯文章' : '新增文章'}</h3>
        <button className="btn btn-secondary ms-auto" onClick={onBack}>回列表</button>
      </div>
      <div className="card-body">
        <form onSubmit={handleSubmit}>
          <div className="row">
            <div className="col-md-8">
              <div className="mb-3">
                <label className="form-label required">文章標題</label>
                <input type="text" className="form-control" name="title" value={formData.title} onChange={handleChange} required />
              </div>
              
              <div className="mb-3">
                <label className="form-label required">網址代稱 (Slug)</label>
                <input type="text" className="form-control" name="slug" value={formData.slug} onChange={handleChange} required />
              </div>

              <div className="mb-3">
                <label className="form-label">內容</label>
                <ReactQuill 
                  ref={quillRef}
                  theme="snow" 
                  value={formData.content} 
                  onChange={handleEditorChange} 
                  modules={modules}
                  style={{ height: '400px', marginBottom: '50px' }}
                />
              </div>
            </div>

            <div className="col-md-4">
              <div className="card">
                <div className="card-body">
                  <div className="mb-3">
                    <label className="form-label">發布設定</label>
                    <select className="form-select mb-2" name="status" value={formData.status} onChange={handleChange}>
                      <option value="draft">草稿</option>
                      <option value="published">已發布</option>
                    </select>
                    <select className="form-select mb-2" name="type" value={formData.type} onChange={handleChange}>
                      <option value="news">最新消息</option>
                      <option value="article">專欄文章</option>
                      <option value="statics">靜態頁面</option>
                    </select>
                  </div>

                  <div className="mb-3">
                    <label className="form-label">發布時間</label>
                    <input type="datetime-local" className="form-control" name="published_at" value={formData.published_at || ''} onChange={handleChange} />
                  </div>

                  <div className="mb-3">
                    <label className="form-label">排序 (越大越前)</label>
                    <input type="number" className="form-control" name="sort_order" value={formData.sort_order} onChange={handleChange} />
                  </div>

                  <div className="mb-3">
                    <label className="form-label">封面圖片</label>
                    {formData.cover_image && (
                      <div className="mb-2">
                        <img src={formData.cover_image} className="img-fluid rounded" alt="Cover" />
                      </div>
                    )}
                    <input type="file" className="form-control" accept="image/*" onChange={handleCoverUpload} />
                    <input type="hidden" name="cover_image" value={formData.cover_image} />
                  </div>

                  <div className="mb-3">
                    <label className="form-label">標籤 (Tags)</label>
                    <input type="text" className="form-control" name="tags" placeholder="逗號分隔..." value={formData.tags} onChange={handleChange} />
                  </div>
                </div>
              </div>
              
              <div className="mt-3">
                <button type="submit" className="btn btn-primary w-100" disabled={loading}>
                  {loading ? '儲存中...' : '儲存文章'}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
}

export default CmsEditor;
