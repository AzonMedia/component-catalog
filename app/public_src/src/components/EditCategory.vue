<template>
    <!--
    :cancel-disabled="action_state"
    :ok-disabled="loading_state"
    :ok-only="action_state && !loading_state"
    -->
    <b-modal
            id="category-modal"
            :title="CategoryData.modal_title"
            header-bg-variant="success"
            header-text-variant="light"
            body-bg-variant="light"
            body-text-variant="dark"
            :ok-title="CategoryData.button_title"
            ok-variant="success"
            centered
            @ok="modal_ok_handler"
            @cancel="modal_cancel_handler"
            @show="modal_show_handler"
            size="lg"
    >
        <p>Category name: <span v-for="(catalog_category_name, catalog_category_uuid) in $parent.CategoryPath">/{{catalog_category_name}}</span>/ <input v-model="catalog_category_name" type="text" placeholder="category name" /></p>
    </b-modal>

</template>

<script>
    export default {
        name: "EditCategory",
        props: {
            CategoryData : Object
        },
        data() {
            return {
                catalog_category_name: '',
            };
        },
        methods: {
            modal_ok_handler(bvModalEvent) {
                let url = this.get_route('GuzabaPlatform\\Catalog\\Models\\Category:crud_action_create')//if this class is inherited then in routes_map.config.js the child will show up
                //let url = '/catalog/category'
                if (this.CategoryData.catalog_category_uuid) {
                    url = this.get_route('GuzabaPlatform\\Catalog\\Models\\Category:crud_action_update', this.CategoryData.catalog_category_uuid)
                    //url = '/catalog/category/' + this.CategoryData.catalog_category_uuid
                }
                let SendValues = {};
                SendValues.catalog_category_name = this.catalog_category_name;
                //SendValues.parent_catalog_category_uuid = null;
                SendValues.parent_catalog_category_uuid = this.$parent.catalog_category_uuid;
                this.$http(
                    {
                        method: this.CategoryData.method,
                        url: url,
                        data: SendValues
                    }
                ).
                then(function() {
                    //do nothing - in the finally it will reload the category
                }).catch( err => {
                    this.$parent.show_toast(err.response.data.message);
                }).finally( () => {
                    this.$parent.get_category_contents(this.$parent.catalog_category_uuid);
                });
            },
            modal_cancel_handler(bvModalEvent) {
                this.catalog_category_name = '';
            },
            modal_show_handler(bvModalEvent) {
                this.catalog_category_name = '';
                if (this.CategoryData.catalog_category_name) {
                    this.catalog_category_name = this.CategoryData.catalog_category_name;
                }
            }
        }
    }
</script>

<style scoped>

</style>