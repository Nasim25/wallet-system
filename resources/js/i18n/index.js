import { createI18n } from "vue-i18n";

const messages = {
    en: {
        login: "Login",
        register: "Register",
        email: "Email address",
        password: "Password",
        logout: "Logout",
        dont_have_account: "Don't have an account?",
    },
    bn: {
        login: "লগইন",
        register: "রেজিস্টার",
        email: "ইমেইল ঠিকানা",
        password: "পাসওয়ার্ড",
        logout: "লগআউট",
        dont_have_account: "কোন অ্যাকাউন্ট নেই?",
    },
};

export const i18n = createI18n({
    legacy: false,
    locale: localStorage.getItem("locale") || "en",
    fallbackLocale: "en",
    messages,
});
