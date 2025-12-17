import React, { useState, useEffect, useRef } from 'react';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';

// Custom Tags Input Component (Reused)
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

function CmsEditor({ id, onBack }) {
  // ... (rest of CmsEditor logic) ...
// Note: Since I am replacing the entire component definition if I use replace, I should be careful. 
// Actually I only need to insert the TagsInput definition BEFORE CmsEditor, and then replace the JSX of the tags input.
// I will use multi_replace for safety and precision.
// But wait, the previous tool call was viewing the file.
// I will just use `replace_file_content` to insert the component and `multi_replace` or another call to fix the JSX.
// Actually, I can do it in two steps.
// Step 1: Insert TagsInput component definition at top level.
// Step 2: Replace the JSX in render.
// LIMITATION: `replace_file_content` works on contiguous block.
// Let's do a smart replacement. I will insert TagsInput before CmsEditor function.
// And then replace the input JSX inside CmsEditor.
// Wait, I can't do two separate non-contiguous edits in one `replace_file_content` call.
// I should use `multi_replace_file_content`.

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
                <label className="form-label">
                  標籤 (逗號分隔)
                </label>
                <div className="mb-2">
                    <TagsInput 
                      value={formData.tags ? formData.tags.split(',').filter(t => t) : []} 
                      onChange={(tags) => setFormData(prev => ({ ...prev, tags: tags.join(',') }))}
                      placeholder='輸入後按 Enter'
                   />
                </div>
                {/* 預設標籤快捷鍵 */}
                <div className="d-flex gap-2">
                    {['活動新品', '展店消息', '會員活動'].map(tag => (
                        <button 
                            key={tag}
                            type="button" 
                            className="btn btn-sm btn-outline-secondary"
                            onClick={() => {
                                const currentTags = formData.tags ? formData.tags.split(',').map(t => t.trim()).filter(Boolean) : [];
                                if (!currentTags.includes(tag)) {
                                    const newTags = [...currentTags, tag].join(',');
                                    setFormData(prev => ({ ...prev, tags: newTags }));
                                }
                            }}
                        >
                            + {tag}
                        </button>
                    ))}
                </div>
              </div>
              
              <div className="mt-3">
                <button type="submit" className="btn btn-primary w-100" disabled={loading}>
                  {loading ? '儲存中...' : '儲存文章'}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
);
}

export default CmsEditor;
