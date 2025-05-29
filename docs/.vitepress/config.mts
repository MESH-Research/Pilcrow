import { defineConfig } from "vitepress";
import { SearchPlugin } from "vitepress-plugin-search";

var options = {
    previewLength: 62,
    buttonLabel: "Search",
    placeholder: "Search docs",
    allow: [],
    ignore: [],
};

// https://vitepress.dev/reference/site-config
export default defineConfig({
    vite: { plugins: [SearchPlugin(options)] },
    title: "Pilcrow Documentation",
    description: "Documentation site for pilcrow.dev",
    themeConfig: {
        // https://vitepress.dev/reference/default-theme-config
        editLink: {
            pattern:
                "https://github.com/mesh-research/pilcrow/edit/master/docs/:path",
        },
        nav: [
            { text: "Home", link: "/" },
            { text: "Guide", link: "/guide/" },
            { text: "Installation", link: "/install/" },
            { text: "Developers", link: "/developers/" },
        ],

        sidebar: {
            "/guide/": [
                {
                    text: "Guide",
                    items: [
                        { text: "Introduction", link: "/guide/" },
                        { text: "Reviewing", link: "/guide/reviewing" },
                        { text: "Notifications", link: "/guide/notifications" },
                        { text: "Contact", link: "/guide/contact" },
                    ],
                },
            ],
            "/developers/": [
                {
                    text: "Developers",
                    items: [
                        { text: "Introduction", link: "/developers/" },
                        {
                            text: "Getting Started",
                            link: "/developers/getting-started",
                        },
                        { text: "Team", link: "/developers/team" },
                        {
                            text: "Architecture",
                            link: "/developers/architecture",
                        },
                        { text: "Testing", link: "/developers/testing" },
                        {
                            text: "Copyediting",
                            link: "/developers/copyediting",
                        },
                        {
                            text: "Documentation",
                            link: "/developers/documentation",
                        },
                        {
                            text: "Staging Environments",
                            link: "/developers/staging",
                        },
                    ],
                },
            ],
            "/install/": [
                {
                    items: [
                        { text: "Installation", link: "/install/" },
                        { text: "Configuration", link: "/install/config" },
                        {
                            text: "Recipes",
                            items: [
                                {
                                    text: "Mysql",
                                    link: "/install/recipes/mysql",
                                },
                                {
                                    text: "SSL/Reverse Proxy",
                                    link: "/install/recipes/proxy",
                                },
                                {
                                    text: "Redis",
                                    link: "/install/recipes/redis",
                                },
                                {
                                    text: "Content Delivery Network",
                                    link: '/install/recipes/cdn/'
                                }
                            ],
                        },
                    ],
                },
            ],
        },

        socialLinks: [
            {
                icon: "github",
                link: "https://github.com/mesh-research/pilcrow",
            },
        ],
    },
});
