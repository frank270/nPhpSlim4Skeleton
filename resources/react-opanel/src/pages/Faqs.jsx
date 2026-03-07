import React, { useEffect, useMemo, useState } from 'react';
import ReactDOM from 'react-dom/client';
import { useTranslation } from 'react-i18next';
import '../i18n';

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

const initialCategoryForm = {
  name: '',
  slug: '',
  sort_order: 0,
  is_active: true,
};

const initialFaqForm = {
  category_id: '',
  question: '',
  answer: '',
  is_highlight: false,
  status: 'draft',
  sort_order: 0,
};

function FaqsApp({ apiBase }) {
  const { t } = useTranslation();
  const [activeTab, setActiveTab] = useState('faqs'); // 'categories' or 'faqs'

  // 分類相關狀態
  const [categories, setCategories] = useState([]);
  const [categoriesLoading, setCategoriesLoading] = useState(true);
  const [showCategoryForm, setShowCategoryForm] = useState(false);
  const [categoryForm, setCategoryForm] = useState(initialCategoryForm);
  const [editingCategory, setEditingCategory] = useState(null);
  const [isSubmittingCategory, setIsSubmittingCategory] = useState(false);

  // FAQ 相關狀態
  const [faqs, setFaqs] = useState([]);
  const [faqsLoading, setFaqsLoading] = useState(true);
  const [faqsError, setFaqsError] = useState('');
  const [refreshKey, setRefreshKey] = useState(0);
  const [showFaqForm, setShowFaqForm] = useState(false);
  const [faqForm, setFaqForm] = useState(initialFaqForm);
  const [editingFaq, setEditingFaq] = useState(null);
  const [isSubmittingFaq, setIsSubmittingFaq] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [targetFaq, setTargetFaq] = useState(null);
  const [filters, setFilters] = useState({
    category_id: '',
    status: '',
    keyword: '',
  });

  const listEndpoint = useMemo(() => `${apiBase}/list`, [apiBase]);
  const categoriesEndpoint = useMemo(() => `${apiBase}/categories`, [apiBase]);

  // 載入分類
  const loadCategories = async () => {
    setCategoriesLoading(true);
    try {
      const response = await fetch(categoriesEndpoint);
      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || t('common.error'));
      }
      setCategories(result.data || []);
    } catch (err) {
      console.error('載入分類失敗:', err);
      setCategories([]);
    } finally {
      setCategoriesLoading(false);
    }
  };

  // 載入 FAQ
  const loadFaqs = async () => {
    setFaqsLoading(true);
    setFaqsError('');
    try {
      const query = new URLSearchParams();
      if (filters.category_id) query.append('category_id', filters.category_id);
      if (filters.status) query.append('status', filters.status);
      if (filters.keyword) query.append('keyword', filters.keyword);

      const response = await fetch(`${listEndpoint}?${query.toString()}`);
      const result = await response.json();
      if (!result.success) {
        throw new Error(result.message || t('common.error'));
      }
      setFaqs(result.data || []);
    } catch (err) {
      setFaqsError(err.message);
      setFaqs([]);
    } finally {
      setFaqsLoading(false);
    }
  };

  useEffect(() => {
    loadCategories();
  }, [categoriesEndpoint]);

  useEffect(() => {
    loadFaqs();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [listEndpoint, refreshKey, filters]);

  // ==================== 分類管理 ====================

  const handleCategoryInputChange = (event) => {
    const { name, value, type, checked } = event.target;
    setCategoryForm((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const resetCategoryForm = () => {
    setCategoryForm(initialCategoryForm);
    setShowCategoryForm(false);
    setEditingCategory(null);
  };

  const handleEditCategory = (category) => {
    setEditingCategory(category);
    setCategoryForm({
      name: category.name || '',
      slug: category.slug || '',
      sort_order: category.sort_order || 0,
      is_active: category.is_active ?? true,
    });
    setShowCategoryForm(true);
  };

  const handleSubmitCategory = async (event) => {
    event.preventDefault();
    setIsSubmittingCategory(true);
    try {
      if (!categoryForm.name.trim()) {
        throw new Error(t('faqs.category_name_required'));
      }
      if (!categoryForm.slug.trim()) {
        throw new Error(t('faqs.slug_required'));
      }

      const payload = {
        name: categoryForm.name.trim(),
        slug: categoryForm.slug.trim(),
        sort_order: parseInt(categoryForm.sort_order) || 0,
        is_active: categoryForm.is_active,
      };

      const endpoint = editingCategory
        ? `${apiBase}/categories/${editingCategory.id}/edit`
        : `${apiBase}/categories/create`;

      const response = await fetch(endpoint, {
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
          throw new Error(json.message || t('common.error_saving_category'));
        } catch {
          throw new Error(text || t('common.error_saving_category'));
        }
      }

      if (!result.success) {
        throw new Error(result.message || t('common.error_saving_category'));
      }

      toast(editingCategory ? t('faqs.category_updated') : t('faqs.category_created'), 'success');
      resetCategoryForm();
      loadCategories();
    } catch (err) {
      toast(err.message || t('common.error_saving_category'), 'error');
    } finally {
      setIsSubmittingCategory(false);
    }
  };

  const handleDeleteCategory = async (category) => {
    if (!window.confirm(t('faqs.delete_category_confirm', { name: category.name }))) {
      return;
    }
    try {
      const response = await fetch(`${apiBase}/categories/${category.id}/delete`, {
        method: 'DELETE',
      });

      let result;
      if (response.ok) {
        result = await response.json();
      } else {
        const text = await response.text();
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || t('common.error_deleting'));
        } catch {
          throw new Error(text || t('common.error_deleting'));
        }
      }

      if (!result.success) {
        throw new Error(result.message || t('common.error_deleting'));
      }

      toast(t('common.success'), 'success');
      loadCategories();
      // 如果刪除的分類正在篩選中，清除篩選
      if (filters.category_id === String(category.id)) {
        setFilters((prev) => ({ ...prev, category_id: '' }));
      }
    } catch (err) {
      toast(err.message || t('common.error_deleting'), 'error');
    }
  };

  // ==================== FAQ 管理 ====================

  const handleFaqInputChange = (event) => {
    const { name, value, type, checked } = event.target;
    setFaqForm((prev) => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const resetFaqForm = () => {
    setFaqForm(initialFaqForm);
    setShowFaqForm(false);
    setEditingFaq(null);
  };

  const handleEditFaq = (faq) => {
    setEditingFaq(faq);
    setFaqForm({
      category_id: faq.category_id ? String(faq.category_id) : '',
      question: faq.question || '',
      answer: faq.answer || '',
      is_highlight: faq.is_highlight ?? false,
      status: faq.status || 'draft',
      sort_order: faq.sort_order || 0,
    });
    setShowFaqForm(true);
  };

  const handleSubmitFaq = async (event) => {
    event.preventDefault();
    setIsSubmittingFaq(true);
    try {
      if (!faqForm.question.trim()) {
        throw new Error(t('faqs.question_required'));
      }
      if (!faqForm.answer.trim()) {
        throw new Error(t('faqs.answer_required'));
      }

      const payload = {
        category_id: faqForm.category_id ? parseInt(faqForm.category_id) : null,
        question: faqForm.question.trim(),
        answer: faqForm.answer.trim(),
        is_highlight: faqForm.is_highlight,
        status: faqForm.status,
        sort_order: parseInt(faqForm.sort_order) || 0,
      };

      const endpoint = editingFaq
        ? `${apiBase}/${editingFaq.id}/edit`
        : `${apiBase}/create`;

      const response = await fetch(endpoint, {
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
          throw new Error(json.message || t('common.error_saving_faq'));
        } catch {
          throw new Error(text || t('common.error_saving_faq'));
        }
      }

      if (!result.success) {
        throw new Error(result.message || t('common.error_saving_faq'));
      }

      toast(editingFaq ? t('faqs.faq_updated') : t('faqs.faq_created'), 'success');
      resetFaqForm();
      setRefreshKey((prev) => prev + 1);
    } catch (err) {
      toast(err.message || t('common.error_saving_faq'), 'error');
    } finally {
      setIsSubmittingFaq(false);
    }
  };

  const handleToggleStatus = async (faq) => {
    try {
      const response = await fetch(`${apiBase}/${faq.id}/toggle-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({}),
      });

      let result;
      if (response.ok) {
        result = await response.json();
      } else {
        const text = await response.text();
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || t('common.error_updating_status'));
        } catch {
          throw new Error(text || t('common.error_updating_status'));
        }
      }

      if (!result.success) {
        throw new Error(result.message || t('common.error_updating_status'));
      }

      toast(t('faqs.status_updated'), 'success');
      setFaqs((prev) => prev.map((item) =>
        item.id === faq.id ? { ...item, status: result.data.status } : item
      ));
    } catch (err) {
      toast(err.message || t('common.error_updating_status'), 'error');
    }
  };

  const handleDeleteFaq = async (faq) => {
    if (!window.confirm(t('faqs.delete_faq_confirm', { name: faq.question }))) {
      return;
    }
    setTargetFaq(faq);
    setIsDeleting(true);
    try {
      const response = await fetch(`${apiBase}/${faq.id}/delete`, {
        method: 'DELETE',
      });

      let result;
      if (response.ok) {
        result = await response.json();
      } else {
        const text = await response.text();
        try {
          const json = JSON.parse(text);
          throw new Error(json.message || t('common.error_deleting'));
        } catch {
          throw new Error(text || t('common.error_deleting'));
        }
      }

      if (!result.success) {
        throw new Error(result.message || t('common.error_deleting'));
      }

      toast(t('common.success'), 'success');
      setFaqs((prev) => prev.filter((item) => item.id !== faq.id));
    } catch (err) {
      toast(err.message || t('common.error_deleting'), 'error');
    } finally {
      setIsDeleting(false);
      setTargetFaq(null);
    }
  };

  const getStatusBadge = (status) => {
    const statusMap = {
      published: { label: t('faqs.status_published'), class: 'bg-success text-white' },
      draft: { label: t('faqs.status_draft'), class: 'bg-secondary text-white' },
    };
    const statusInfo = statusMap[status] || { label: status, class: 'bg-secondary text-white' };
    return <span className={`badge ${statusInfo.class}`}>{statusInfo.label}</span>;
  };

  return (
    <div>
      {/* 標籤頁切換 */}
      <div className="card mb-3">
        <div className="card-body">
          <ul className="nav nav-tabs" role="tablist">
            <li className="nav-item">
              <button
                className={`nav-link ${activeTab === 'faqs' ? 'active' : ''}`}
                onClick={() => setActiveTab('faqs')}
                type="button"
              >
                {t('faqs.tab_faqs')}
              </button>
            </li>
            <li className="nav-item">
              <button
                className={`nav-link ${activeTab === 'categories' ? 'active' : ''}`}
                onClick={() => setActiveTab('categories')}
                type="button"
              >
                {t('faqs.tab_categories')}
              </button>
            </li>
          </ul>
        </div>
      </div>

      {/* 問題管理標籤頁 */}
      {activeTab === 'faqs' && (
        <div>
          {/* 工具列 */}
          <div className="d-flex justify-content-between align-items-center mb-3">
            <h3 className="mb-0">{t('faqs.list_title')}</h3>
            <button
              className="btn btn-primary"
              onClick={() => {
                resetFaqForm();
                setShowFaqForm(true);
              }}
            >
              {t('faqs.add_faq')}
            </button>
          </div>

          {/* 篩選器 */}
          <div className="card mb-3">
            <div className="card-body">
              <div className="row g-3">
                <div className="col-md-4">
                  <label className="form-label">{t('faqs.category')}</label>
                  <select
                    className="form-select"
                    value={filters.category_id}
                    onChange={(e) => setFilters((prev) => ({ ...prev, category_id: e.target.value }))}
                  >
                    <option value="">{t('common.all')}</option>
                    {categories.map((cat) => (
                      <option key={cat.id} value={cat.id}>
                        {cat.name}
                      </option>
                    ))}
                  </select>
                </div>
                <div className="col-md-4">
                  <label className="form-label">{t('faqs.status')}</label>
                  <select
                    className="form-select"
                    value={filters.status}
                    onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
                  >
                    <option value="">{t('common.all')}</option>
                    <option value="published">{t('faqs.status_published')}</option>
                    <option value="draft">{t('faqs.status_draft')}</option>
                  </select>
                </div>
                <div className="col-md-4">
                  <label className="form-label">{t('faqs.keyword')}</label>
                  <input
                    type="text"
                    className="form-control"
                    placeholder={t('faqs.keyword_placeholder')}
                    value={filters.keyword}
                    onChange={(e) => setFilters((prev) => ({ ...prev, keyword: e.target.value }))}
                  />
                </div>
              </div>
            </div>
          </div>

          {/* 問題表單 */}
          {showFaqForm && (
            <div className="card mb-3">
              <div className="card-header">
                <h3 className="card-title">{editingFaq ? t('faqs.edit_faq') : t('faqs.add_faq')}</h3>
              </div>
              <div className="card-body">
                <form onSubmit={handleSubmitFaq}>
                  <div className="row g-3">
                    <div className="col-md-6">
                      <label className="form-label">{t('faqs.category')}</label>
                      <select
                        className="form-select"
                        name="category_id"
                        value={faqForm.category_id}
                        onChange={handleFaqInputChange}
                        disabled={isSubmittingFaq}
                      >
                        <option value="">{t('faqs.uncategorized')}</option>
                        {categories.map((cat) => (
                          <option key={cat.id} value={cat.id}>
                            {cat.name}
                          </option>
                        ))}
                      </select>
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">{t('faqs.status')}</label>
                      <select
                        className="form-select"
                        name="status"
                        value={faqForm.status}
                        onChange={handleFaqInputChange}
                        disabled={isSubmittingFaq}
                      >
                        <option value="draft">{t('faqs.status_draft')}</option>
                        <option value="published">{t('faqs.status_published')}</option>
                      </select>
                    </div>
                    <div className="col-12">
                      <label className="form-label">{t('faqs.question')}<span className="text-danger">*</span></label>
                      <input
                        type="text"
                        className="form-control"
                        name="question"
                        value={faqForm.question}
                        onChange={handleFaqInputChange}
                        disabled={isSubmittingFaq}
                        required
                        maxLength={255}
                      />
                    </div>
                    <div className="col-12">
                      <label className="form-label">{t('faqs.answer')}<span className="text-danger">*</span></label>
                      <textarea
                        className="form-control"
                        name="answer"
                        value={faqForm.answer}
                        onChange={handleFaqInputChange}
                        disabled={isSubmittingFaq}
                        required
                        rows={6}
                      />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">{t('faqs.sort_order')}</label>
                      <input
                        type="number"
                        className="form-control"
                        name="sort_order"
                        value={faqForm.sort_order}
                        onChange={handleFaqInputChange}
                        disabled={isSubmittingFaq}
                        min={0}
                      />
                    </div>
                    <div className="col-md-6">
                      <div className="form-check mt-4">
                        <input
                          type="checkbox"
                          className="form-check-input"
                          name="is_highlight"
                          checked={faqForm.is_highlight}
                          onChange={handleFaqInputChange}
                          disabled={isSubmittingFaq}
                        />
                        <label className="form-check-label">{t('faqs.is_highlight')}</label>
                      </div>
                    </div>
                  </div>
                  <div className="mt-3">
                    <button type="submit" className="btn btn-primary" disabled={isSubmittingFaq}>
                      {isSubmittingFaq ? t('common.saving') : t('common.save')}
                    </button>
                    <button
                      type="button"
                      className="btn btn-secondary ms-2"
                      onClick={resetFaqForm}
                      disabled={isSubmittingFaq}
                    >
                      {t('common.cancel')}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}

          {/* 問題列表 */}
          {faqsLoading ? (
            <div className="text-center py-5">
              <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">{t('common.loading')}</span>
              </div>
            </div>
          ) : faqsError ? (
            <div className="alert alert-danger">{faqsError}</div>
          ) : faqs.length === 0 ? (
            <div className="text-center py-5 text-muted">{t('faqs.no_faqs')}</div>
          ) : (
            <div className="card">
              <div className="table-responsive">
                <table className="table table-hover">
                  <thead>
                    <tr>
                      <th>{t('faqs.question')}</th>
                      <th>{t('faqs.category')}</th>
                      <th>{t('faqs.status')}</th>
                      <th>{t('faqs.is_highlight_short')}</th>
                      <th>{t('faqs.sort_order')}</th>
                      <th className="text-end">{t('common.actions')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {faqs.map((faq) => (
                      <tr key={faq.id}>
                        <td>
                          <div className="fw-bold">{faq.question}</div>
                          <div className="text-secondary small" style={{ maxWidth: '400px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                            {faq.answer}
                          </div>
                        </td>
                        <td>{faq.category_name || <span className="text-muted">{t('faqs.uncategorized')}</span>}</td>
                        <td>{getStatusBadge(faq.status)}</td>
                        <td>
                          {faq.is_highlight ? (
                            <span className="badge bg-warning text-white">{t('common.yes')}</span>
                          ) : (
                            <span className="text-muted">{t('common.no')}</span>
                          )}
                        </td>
                        <td>{faq.sort_order}</td>
                        <td className="text-end">
                          <div className="btn-group">
                            <button
                              type="button"
                              className="btn btn-sm btn-ghost-primary"
                              onClick={() => handleToggleStatus(faq)}
                            >
                              {faq.status === 'published' ? t('faqs.set_draft') : t('faqs.publish')}
                            </button>
                            <button
                              type="button"
                              className="btn btn-sm btn-ghost-primary"
                              onClick={() => handleEditFaq(faq)}
                            >
                              {t('common.edit')}
                            </button>
                            <button
                              type="button"
                              className="btn btn-sm btn-ghost-danger"
                              onClick={() => handleDeleteFaq(faq)}
                              disabled={isDeleting && targetFaq?.id === faq.id}
                            >
                              {isDeleting && targetFaq?.id === faq.id ? t('common.deleting') : t('common.delete')}
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}

      {/* 分類管理標籤頁 */}
      {activeTab === 'categories' && (
        <div>
          {/* 工具列 */}
          <div className="d-flex justify-content-between align-items-center mb-3">
            <h3 className="mb-0">{t('faqs.category_list')}</h3>
            <button
              className="btn btn-primary"
              onClick={() => {
                resetCategoryForm();
                setShowCategoryForm(true);
              }}
            >
              {t('faqs.add_category')}
            </button>
          </div>

          {/* 分類表單 */}
          {showCategoryForm && (
            <div className="card mb-3">
              <div className="card-header">
                <h3 className="card-title">{editingCategory ? t('faqs.edit_category') : t('faqs.add_category')}</h3>
              </div>
              <div className="card-body">
                <form onSubmit={handleSubmitCategory}>
                  <div className="row g-3">
                    <div className="col-md-6">
                      <label className="form-label">{t('faqs.category_name')}<span className="text-danger">*</span></label>
                      <input
                        type="text"
                        className="form-control"
                        name="name"
                        value={categoryForm.name}
                        onChange={handleCategoryInputChange}
                        disabled={isSubmittingCategory}
                        required
                        maxLength={120}
                      />
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">Slug<span className="text-danger">*</span></label>
                      <input
                        type="text"
                        className="form-control"
                        name="slug"
                        value={categoryForm.slug}
                        onChange={handleCategoryInputChange}
                        disabled={isSubmittingCategory}
                        required
                        maxLength={120}
                        pattern="[a-z0-9-]+"
                        placeholder="例如：general"
                      />
                      <small className="form-hint">{t('faqs.slug_hint')}</small>
                    </div>
                    <div className="col-md-6">
                      <label className="form-label">{t('faqs.sort_order')}</label>
                      <input
                        type="number"
                        className="form-control"
                        name="sort_order"
                        value={categoryForm.sort_order}
                        onChange={handleCategoryInputChange}
                        disabled={isSubmittingCategory}
                        min={0}
                      />
                    </div>
                    <div className="col-md-6">
                      <div className="form-check mt-4">
                        <input
                          type="checkbox"
                          className="form-check-input"
                          name="is_active"
                          checked={categoryForm.is_active}
                          onChange={handleCategoryInputChange}
                          disabled={isSubmittingCategory}
                        />
                        <label className="form-check-label">{t('faqs.is_active')}</label>
                      </div>
                    </div>
                  </div>
                  <div className="mt-3">
                    <button type="submit" className="btn btn-primary" disabled={isSubmittingCategory}>
                      {isSubmittingCategory ? t('common.saving') : t('common.save')}
                    </button>
                    <button
                      type="button"
                      className="btn btn-secondary ms-2"
                      onClick={resetCategoryForm}
                      disabled={isSubmittingCategory}
                    >
                      {t('common.cancel')}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          )}

          {/* 分類列表 */}
          {categoriesLoading ? (
            <div className="text-center py-5">
              <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">{t('common.loading')}</span>
              </div>
            </div>
          ) : categories.length === 0 ? (
            <div className="alert alert-info">{t('faqs.no_categories')}</div>
          ) : (
            <div className="card">
              <div className="table-responsive">
                <table className="table table-hover">
                  <thead>
                    <tr>
                      <th>{t('faqs.name')}</th>
                      <th>Slug</th>
                      <th>{t('faqs.status')}</th>
                      <th>{t('faqs.sort_order')}</th>
                      <th className="text-end">{t('common.actions')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {categories.map((category) => (
                      <tr key={category.id}>
                        <td>{category.name}</td>
                        <td><code>{category.slug}</code></td>
                        <td>
                          {category.is_active ? (
                            <span className="badge bg-success text-white">{t('faqs.status_active')}</span>
                          ) : (
                            <span className="badge bg-secondary text-white">{t('faqs.status_inactive')}</span>
                          )}
                        </td>
                        <td>{category.sort_order}</td>
                        <td className="text-end">
                          <div className="btn-group">
                            <button
                              type="button"
                              className="btn btn-sm btn-ghost-primary"
                              onClick={() => handleEditCategory(category)}
                            >
                              {t('common.edit')}
                            </button>
                            <button
                              type="button"
                              className="btn btn-sm btn-ghost-danger"
                              onClick={() => handleDeleteCategory(category)}
                            >
                              {t('common.delete')}
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

const mountNode = document.getElementById('faqs-app');
if (mountNode) {
  const apiBase = mountNode.dataset.apiBase;
  ReactDOM.createRoot(mountNode).render(
    <React.StrictMode>
      <FaqsApp apiBase={apiBase} />
    </React.StrictMode>
  );
}

