<template>
    <form :id="id"
          :action="actionUri"
          method="post"
          target="_blank"
          @submit.prevent="submit">
        <div class="font-bold text-center py-2">{{__(name.toString().toPhrase())}}</div>

        <div>
            <slot name="upperFields"></slot>

            <item v-if="dataLoaded"
                  noBorder="true"
                  :item="item"
                  :formFields="formFields"
                  :requiredFields="requiredFields"
                  :controller-name="controllerName"></item>

            <slot name="lowerFields"></slot>

            <slot name="submit"></slot>
        </div>

        <input type="hidden" name="_token" :value="$page.props._token"/>
    </form>
</template>

<script>
    import Item from './Item';

    export default {
        components: {
            Item,
        },

        inject: [
            'controllerName',
        ],

        props: {
            name: {
                default: null,
            },
            item: {
                default: null,
            },
            modal: {
                default: {},
            },
        },

        data()
        {
            return {
                id: this.name + '-' + this.item.id,
                getFieldsUri: '/get-options/doc.' + this.controllerName + '/' + this.name + '/' + this.item.id,
                actionUri: '/print/' + this.name + '/' + this.item.id,
                formFields: {},
                requiredFields: [],
                isNotFields: ['type', 'show', 'repeatable'],
                errors: {},
                dataLoaded: false,
            };
        },

        mounted()
        {
            const axios = require('axios');

            axios.post(this.getFieldsUri).then(response => {
                this.formFields = response.data;

                this.requiredFields = this.formFields.requiredFields;
                delete this.formFields.requiredFields;
            }).then(() => {
                this.dataLoaded = true;
            });
        },

        computed: {
            console: () => console,
        },

        methods: {
            submit()
            {
                if (!this.validateRequiredFields(this.requiredFields, this.$el, this.errors)) {
                    return false;
                }

                this.$emit('closeModalFromDocForm', this.name.toPascalCase());
                this.$emit('addFieldStateFromDocForm', this.name.toPascalCase(), this.item.id);
                document.getElementById(this.id).submit();
            },
        },

//        watch: {
//            errors: {
//                immediate: true,
//                handler: function (newVal, oldVal) {
//                    console.log('new: %s, old: %s', newVal, oldVal);
//                },
//            },
//        },
    };
</script>