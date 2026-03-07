/**
 * 台灣地址選擇器
 * 根據郵遞區號 JSON 資料生成三個 datalist 欄位（城市、行政區、郵遞區號）
 * 
 * @param {Object} options - 設定選項
 * @param {string} options.containerId - 容器元素 ID（必填）
 * @param {string} options.jsonUrl - JSON 資料檔案路徑（預設：/js/data/taiwan-zipcode-data.json）
 * @param {Object} options.classes - 自訂 CSS class
 * @param {string} options.classes.county - 城市欄位的 class
 * @param {string} options.classes.district - 行政區欄位的 class
 * @param {string} options.classes.zipcode - 郵遞區號欄位的 class
 * @param {Object} options.labels - 自訂標籤文字
 * @param {string} options.labels.county - 城市標籤（預設：縣市）
 * @param {string} options.labels.district - 行政區標籤（預設：行政區）
 * @param {string} options.labels.zipcode - 郵遞區號標籤（預設：郵遞區號）
 * @param {Object} options.placeholders - 自訂 placeholder
 * @param {string} options.placeholders.county - 城市 placeholder
 * @param {string} options.placeholders.district - 行政區 placeholder
 * @param {string} options.placeholders.zipcode - 郵遞區號 placeholder
 * @param {Object} options.values - 預設值
 * @param {string} options.values.county - 預設城市
 * @param {string} options.values.district - 預設行政區
 * @param {string} options.values.zipcode - 預設郵遞區號
 * @param {Function} options.onChange - 變更回呼函數 (county, district, zipcode) => {}
 * 
 * @example
 * const selector = new TaiwanAddressSelector({
 *   containerId: 'address-container',
 *   classes: {
 *     county: 'form-control',
 *     district: 'form-control',
 *     zipcode: 'form-control'
 *   },
 *   onChange: (county, district, zipcode) => {
 *     console.log(county, district, zipcode);
 *   }
 * });
 */
class TaiwanAddressSelector {
  constructor(options = {}) {
    this.options = {
      containerId: options.containerId || null,
      jsonUrl: options.jsonUrl || '/js/data/taiwan-zipcode-data.json',
      classes: {
        county: options.classes?.county || 'form-control',
        district: options.classes?.district || 'form-control',
        zipcode: options.classes?.zipcode || 'form-control',
        ...options.classes
      },
      labels: {
        county: options.labels?.county || '縣市',
        district: options.labels?.district || '行政區',
        zipcode: options.labels?.zipcode || '郵遞區號',
        ...options.labels
      },
      placeholders: {
        county: options.placeholders?.county || '請選擇或輸入縣市',
        district: options.placeholders?.district || '請選擇或輸入行政區',
        zipcode: options.placeholders?.zipcode || '請選擇或輸入郵遞區號',
        ...options.placeholders
      },
      values: {
        county: options.values?.county || '',
        district: options.values?.district || '',
        zipcode: options.values?.zipcode || '',
        ...options.values
      },
      onChange: options.onChange || null
    };

    this.data = null;
    this.container = null;
    this.countyInput = null;
    this.districtInput = null;
    this.zipcodeInput = null;
    this.countyDatalist = null;
    this.districtDatalist = null;
    this.zipcodeDatalist = null;

    if (!this.options.containerId) {
      throw new Error('containerId 是必填參數');
    }

    this.init();
  }

  async init() {
    // 取得容器
    this.container = document.getElementById(this.options.containerId);
    if (!this.container) {
      throw new Error(`找不到 ID 為 "${this.options.containerId}" 的容器元素`);
    }

    // 載入 JSON 資料
    try {
      const response = await fetch(this.options.jsonUrl);
      if (!response.ok) {
        throw new Error(`無法載入 JSON 資料: ${response.statusText}`);
      }
      this.data = await response.json();
    } catch (error) {
      console.error('載入郵遞區號資料失敗:', error);
      throw error;
    }

    // 生成 HTML
    this.render();
    
    // 綁定事件
    this.bindEvents();
    
    // 設定預設值
    if (this.options.values.county) {
      this.setCounty(this.options.values.county);
    }
    if (this.options.values.district) {
      this.setDistrict(this.options.values.district);
    }
    if (this.options.values.zipcode) {
      this.setZipcode(this.options.values.zipcode);
    }
  }

  render() {
    const containerId = this.options.containerId;
    const uniqueId = containerId.replace(/[^a-zA-Z0-9]/g, '_');

    // 生成 HTML
    this.container.innerHTML = `
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">${this.options.labels.county}</label>
          <input 
            type="text" 
            id="${uniqueId}_county" 
            class="${this.options.classes.county}" 
            list="${uniqueId}_county_list"
            placeholder="${this.options.placeholders.county}"
            autocomplete="off"
          />
          <datalist id="${uniqueId}_county_list"></datalist>
        </div>
        <div class="col-md-4">
          <label class="form-label">${this.options.labels.district}</label>
          <input 
            type="text" 
            id="${uniqueId}_district" 
            class="${this.options.classes.district}" 
            list="${uniqueId}_district_list"
            placeholder="${this.options.placeholders.district}"
            autocomplete="off"
            disabled
          />
          <datalist id="${uniqueId}_district_list"></datalist>
        </div>
        <div class="col-md-4">
          <label class="form-label">${this.options.labels.zipcode}</label>
          <input 
            type="text" 
            id="${uniqueId}_zipcode" 
            class="${this.options.classes.zipcode}" 
            list="${uniqueId}_zipcode_list"
            placeholder="${this.options.placeholders.zipcode}"
            autocomplete="off"
            disabled
            maxlength="10"
          />
          <datalist id="${uniqueId}_zipcode_list"></datalist>
        </div>
      </div>
    `;

    // 取得元素參考
    this.countyInput = document.getElementById(`${uniqueId}_county`);
    this.districtInput = document.getElementById(`${uniqueId}_district`);
    this.zipcodeInput = document.getElementById(`${uniqueId}_zipcode`);
    this.countyDatalist = document.getElementById(`${uniqueId}_county_list`);
    this.districtDatalist = document.getElementById(`${uniqueId}_district_list`);
    this.zipcodeDatalist = document.getElementById(`${uniqueId}_zipcode_list`);

    // 填充城市選單
    this.populateCounties();
  }

  populateCounties() {
    if (!this.countyDatalist) return;

    this.countyDatalist.innerHTML = '';
    const counties = Object.keys(this.data).sort();

    counties.forEach(county => {
      const option = document.createElement('option');
      option.value = county;
      this.countyDatalist.appendChild(option);
    });
  }

  populateDistricts(county) {
    if (!this.districtDatalist || !this.data[county]) return;

    this.districtDatalist.innerHTML = '';
    const districts = Object.keys(this.data[county]).sort();

    districts.forEach(district => {
      const option = document.createElement('option');
      option.value = district;
      this.districtDatalist.appendChild(option);
    });

    // 啟用行政區欄位
    if (this.districtInput) {
      this.districtInput.disabled = false;
    }
  }

  populateZipcodes(county, district) {
    if (!this.zipcodeDatalist || !this.data[county] || !this.data[county][district]) return;

    this.zipcodeDatalist.innerHTML = '';
    const zipcode = this.data[county][district];

    const option = document.createElement('option');
    option.value = zipcode;
    this.zipcodeDatalist.appendChild(option);

    // 自動填入郵遞區號
    if (this.zipcodeInput) {
      this.zipcodeInput.value = zipcode;
    }

    // 啟用郵遞區號欄位
    if (this.zipcodeInput) {
      this.zipcodeInput.disabled = false;
    }
  }

  bindEvents() {
    // 城市變更事件
    if (this.countyInput) {
      this.countyInput.addEventListener('input', (e) => {
        const county = e.target.value.trim();
        
        // 清空行政區和郵遞區號
        if (this.districtInput) {
          this.districtInput.value = '';
          this.districtInput.disabled = true;
        }
        if (this.zipcodeInput) {
          this.zipcodeInput.value = '';
          this.zipcodeInput.disabled = true;
        }

        // 如果選擇的城市存在，更新行政區選單
        if (this.data[county]) {
          this.populateDistricts(county);
        }

        this.triggerChange();
      });
    }

    // 行政區變更事件
    if (this.districtInput) {
      this.districtInput.addEventListener('input', (e) => {
        const county = this.countyInput?.value.trim() || '';
        const district = e.target.value.trim();

        // 清空郵遞區號
        if (this.zipcodeInput) {
          this.zipcodeInput.value = '';
          this.zipcodeInput.disabled = true;
        }

        // 如果選擇的行政區存在，更新郵遞區號
        if (this.data[county] && this.data[county][district]) {
          this.populateZipcodes(county, district);
        }

        this.triggerChange();
      });
    }

    // 郵遞區號變更事件
    if (this.zipcodeInput) {
      this.zipcodeInput.addEventListener('input', () => {
        this.triggerChange();
      });
    }
  }

  triggerChange() {
    if (this.options.onChange && typeof this.options.onChange === 'function') {
      const county = this.countyInput?.value.trim() || '';
      const district = this.districtInput?.value.trim() || '';
      const zipcode = this.zipcodeInput?.value.trim() || '';
      this.options.onChange(county, district, zipcode);
    }
  }

  // 公開方法：設定城市
  setCounty(county) {
    if (this.countyInput && this.data[county]) {
      this.countyInput.value = county;
      this.populateDistricts(county);
      this.triggerChange();
    }
  }

  // 公開方法：設定行政區
  setDistrict(district) {
    const county = this.countyInput?.value.trim() || '';
    if (this.districtInput && this.data[county] && this.data[county][district]) {
      this.districtInput.value = district;
      this.districtInput.disabled = false;
      this.populateZipcodes(county, district);
      this.triggerChange();
    }
  }

  // 公開方法：設定郵遞區號
  setZipcode(zipcode) {
    if (this.zipcodeInput) {
      this.zipcodeInput.value = zipcode;
      this.zipcodeInput.disabled = false;
      this.triggerChange();
    }
  }

  // 公開方法：取得目前值
  getValues() {
    return {
      county: this.countyInput?.value.trim() || '',
      district: this.districtInput?.value.trim() || '',
      zipcode: this.zipcodeInput?.value.trim() || ''
    };
  }

  // 公開方法：重置
  reset() {
    if (this.countyInput) {
      this.countyInput.value = '';
    }
    if (this.districtInput) {
      this.districtInput.value = '';
      this.districtInput.disabled = true;
    }
    if (this.zipcodeInput) {
      this.zipcodeInput.value = '';
      this.zipcodeInput.disabled = true;
    }
    this.triggerChange();
  }
}

// 如果是在瀏覽器環境，將類別暴露到全域
if (typeof window !== 'undefined') {
  window.TaiwanAddressSelector = TaiwanAddressSelector;
}

