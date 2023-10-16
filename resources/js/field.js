Nova.booting((app, store) => {
    app.component("DetailField", require("./components/DetailField.vue").default);
    app.component("FormField", require("./components/FormField.vue").default);
})
