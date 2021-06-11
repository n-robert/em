<template>
    <div :class="backLayer">
        <ul :class="tabLayer" class="flex w-full">
            <li v-for="(fieldGroup, name) in formFields"
                v-if="fieldGroup.type === 'fieldgroup'"
                :class="selected[name] ? tabActive : tabInActive"
                @click="selectTab(name)"
                class="cursor-pointer w-full">
                <span>
                    {{__(name).toString().toPhrase()}}
                </span>
            </li>
        </ul>

        <div :class="frontLayer">
            <div v-for="(element, key) in formFields">
                <tab v-if="element.type === 'fieldgroup'"
                     :key="key"
                     :selected="selected[key]">
                    <div v-for="(field, name) in element"
                         v-if="!isNotFields.includes(name)"
                         class="table-row"
                         :class="name">
                        <field-set
                                v-if="field.type === 'fieldset'"
                                v-show="field.show"
                                :field="field"
                                :name="name"
                                :item="item"
                                :key="fieldSetKey"
                                :controllerName="controllerName"
                                @addItem="addItem"
                                @removeItem="removeItem"></field-set>

                        <fmsdocs-input v-else
                                       :name="field.name"
                                       :type="field.type"
                                       :value="item[field.name] || field.value"
                                       :options="field.options"
                                       :onclick="field.onclick"
                                       :onchange="field.onchange"
                                       :label="field.label"
                                       :hasLabel="field.hasLabel"
                                       :checked="field.checked"
                                       :id="field.name.toString().toKebabCase()"
                                       :isRequired="field.required"
                                       :parenId="field.parent_id"
                                       :show="field.show"></fmsdocs-input>
                    </div>
                </tab>

                <div v-else class="table-row-group">
                    <div v-if="!isNotFields.includes(key)" class="table-row">
                        <field-set
                                v-if="element.type === 'fieldset'"
                                v-show="element.show"
                                :field="element"
                                :name="key"
                                :id="key"
                                :item="item"
                                :key="fieldSetKey"
                                :controllerName="controllerName"
                                @addItem="addItem"
                                @removeItem="removeItem"></field-set>

                        <div v-else>
                            <fmsdocs-input :name="element.name"
                                           :type="element.type"
                                           :value="item[element.name] || element.value"
                                           :options="element.options"
                                           :onclick="element.onclick"
                                           :onchange="element.onchange"
                                           :label="element.label"
                                           :hasLabel="element.hasLabel"
                                           :checked="element.checked"
                                           :id="element.name && element.name.toString().toKebabCase()"
                                           :isRequired="element.required"
                                           :parenId="element.parent_id"
                                           :show="element.show"></fmsdocs-input>
                        </div>
                    </div>
                </div>
            </div>

            <slot></slot>
        </div>
    </div>
</template>

<script>
    import Tab from './Tab';
    import FieldSet from './FieldSet';
    import FmsdocsInput from './Input';

    export default {
        components: {
            Tab,
            FieldSet,
            FmsdocsInput,
        },

        props: {
            item: {
                default: {},
            },
            formFields: {
                default: {},
            },
            requiredFields: {
                default: {},
            },
            controllerName: {
                default: null,
            },
            noBorder: {
                default: false,
            },
        },

        data() {
            const hasTab = this.formFields['has_tabs'];
            let selected = {}, backLayer, frontLayer, tabLayer, tabActive, tabInActive;

            for (const key in this.formFields) {
                if (this.formFields.hasOwnProperty(key)) {
                    selected[key] = !!this.formFields[key]['show'];
                }
            }

            if (this.noBorder) {
                backLayer = '';
                frontLayer = hasTab ? 'table w-full p-2 rounded-b-lg bg-white' : '';
                tabLayer = hasTab ? 'm-4 rounded-lg bg-gradient-to-b from-indigo-100 to-white' : '';
                tabActive = 'py-2 px-8 rounded-t-lg bg-white text-indigo-500';
                tabInActive = 'py-2 px-8 rounded-t-lg hover:text-indigo-500';
            } else {
                backLayer = hasTab ? 'shadow-xl m-4 sm:rounded-lg' : 'shadow-xl m-4 sm:rounded-lg';
                frontLayer =
                    hasTab ?
                        'table w-full p-4 rounded-b-lg bg-white' :
                        'table w-full p-4 pt-6 rounded-lg bg-white';
                tabLayer = 'bg-gradient-to-b rounded-t-lg from-indigo-100 to-white';
                tabActive = 'py-4 px-8 rounded-t-lg bg-white text-indigo-500';
                tabInActive = 'py-4 px-8 rounded-lg hover:text-indigo-500';
            }

            return {
                hasTab,
                selected,
                backLayer,
                frontLayer,
                tabLayer,
                isNotFields: ['type', 'show', 'repeatable', 'upper_case', 'justify', 'split_word', 'cells', 'has_tabs'],
                fieldSetKey: 0,
                tabActive,
                tabInActive,
            };
        },

        methods: {
            selectTab(fieldGroupName) {
                for (const index in this.selected) {
                    if (this.selected.hasOwnProperty(index)) {
                        this.selected[index] = (index === fieldGroupName);
                    }
                }
            },

            addItem(fieldSetName) {
                this.$emit('addItem', fieldSetName);
                this.fieldSetKey++;
            },

            removeItem(fieldSetName, index) {
                this.$emit('removeItem', fieldSetName, index);
                this.fieldSetKey++;
            },
        },
    };
</script>