<template>
    <div class="container">
        <div class="row">
            <div class="col-1">
                <b-icon-folder class="clickable" @click="$parent.open_category(CategoryData.meta_object_uuid)"></b-icon-folder>
            </div>
            <div class="col-1">
                {{CategoryData.catalog_category_id}}
            </div>
            <div class="col-8" style="text-align: left">
                <span class="clickable" @click="$parent.open_category(CategoryData.meta_object_uuid)">{{CategoryData.catalog_category_name}}</span>
            </div>
            <div class="col-2" v-if="typeof $parent.EmbeddedData === 'undefined' ">
                <b-icon-list-check
                        v-b-tooltip.hover title="Category permissions"
                        variant="primary"
                        class="clickable"
                        @click="$parent.permissions_category(CategoryData.meta_object_uuid, CategoryData.catalog_category_name)"
                ></b-icon-list-check>
                <b-icon-pencil
                        v-b-tooltip.hover title="Edit category"
                        variant="primary"
                        class="clickable"
                        @click="$parent.edit_category(CategoryData.meta_object_uuid, CategoryData.catalog_category_name)"
                ></b-icon-pencil>
                <b-icon-trash
                        v-b-tooltip.hover title="Delete category"
                        variant="danger"
                        class="clickable"
                        @click="$parent.delete_category(CategoryData.meta_object_uuid, CategoryData.catalog_category_name)"
                ></b-icon-trash>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Category",
        props: {
            CategoryData : Object
        },
    }
</script>

<style scoped>
    .container {
        border: 2px solid black;
        margin: 4px;

        border-radius: 5px;
        vertical-align: center;
        font-size: 1.5em;
        text-align: left;
    }

    .clickable:hover {

    }

    .clickable {
        cursor: pointer;
    }
</style>