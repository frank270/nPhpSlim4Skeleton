import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';

// zh-TW
import commonTW from './locales/zh-TW/common.json';
import locationsTW from './locales/zh-TW/locations.json';
import accessRolesTW from './locales/zh-TW/access_roles.json';
import adminUsersTW from './locales/zh-TW/admin_users.json';
import externalLinksTW from './locales/zh-TW/external_links.json';
import mediaLibraryTW from './locales/zh-TW/media_library.json';
import faqsTW from './locales/zh-TW/faqs.json';
import cmsCategoriesTW from './locales/zh-TW/cms_categories.json';

// zh-CN
import commonCN from './locales/zh-CN/common.json';
import locationsCN from './locales/zh-CN/locations.json';
import accessRolesCN from './locales/zh-CN/access_roles.json';
import adminUsersCN from './locales/zh-CN/admin_users.json';
import externalLinksCN from './locales/zh-CN/external_links.json';
import mediaLibraryCN from './locales/zh-CN/media_library.json';
import faqsCN from './locales/zh-CN/faqs.json';
import cmsCategoriesCN from './locales/zh-CN/cms_categories.json';

// en
import commonEN from './locales/en/common.json';
import locationsEN from './locales/en/locations.json';
import accessRolesEN from './locales/en/access_roles.json';
import adminUsersEN from './locales/en/admin_users.json';
import externalLinksEN from './locales/en/external_links.json';
import mediaLibraryEN from './locales/en/media_library.json';
import faqsEN from './locales/en/faqs.json';
import cmsCategoriesEN from './locales/en/cms_categories.json';

// ko
import commonKO from './locales/ko/common.json';
import locationsKO from './locales/ko/locations.json';
import accessRolesKO from './locales/ko/access_roles.json';
import adminUsersKO from './locales/ko/admin_users.json';
import externalLinksKO from './locales/ko/external_links.json';
import mediaLibraryKO from './locales/ko/media_library.json';
import faqsKO from './locales/ko/faqs.json';
import cmsCategoriesKO from './locales/ko/cms_categories.json';

const resources = {
  'zh-TW': {
    translation: {
      common: commonTW,
      locations: locationsTW,
      access_roles: accessRolesTW,
      admin_users: adminUsersTW,
      external_links: externalLinksTW,
      media_library: mediaLibraryTW,
      faqs: faqsTW,
      cms_categories: cmsCategoriesTW
    }
  },
  'zh-CN': {
    translation: {
      common: commonCN,
      locations: locationsCN,
      access_roles: accessRolesCN,
      admin_users: adminUsersCN,
      external_links: externalLinksCN,
      media_library: mediaLibraryCN,
      faqs: faqsCN,
      cms_categories: cmsCategoriesCN
    }
  },
  'en': {
    translation: {
      common: commonEN,
      locations: locationsEN,
      access_roles: accessRolesEN,
      admin_users: adminUsersEN,
      external_links: externalLinksEN,
      media_library: mediaLibraryEN,
      faqs: faqsEN,
      cms_categories: cmsCategoriesEN
    }
  },
  'ko': {
    translation: {
      common: commonKO,
      locations: locationsKO,
      access_roles: accessRolesKO,
      admin_users: adminUsersKO,
      external_links: externalLinksKO,
      media_library: mediaLibraryKO,
      faqs: faqsKO,
      cms_categories: cmsCategoriesKO
    }
  }
};

i18n
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    resources,
    fallbackLng: 'zh-TW',
    debug: import.meta.env.DEV,
    
    detection: {
      order: ['cookie', 'navigator'],
      lookupCookie: 'i18next',
      caches: ['cookie'],
    },

    interpolation: {
      escapeValue: false
    }
  });

export default i18n;
