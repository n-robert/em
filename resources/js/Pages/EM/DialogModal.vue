<template>
    <modal :show="show" :max-width="maxWidth" :closeable="closeable" :position="position" @closeModal="closeModal">
        <div class="px-6 py-4">
            <em-button class="relative float-right cursor-pointer m-1" @click.native="closeModal">X</em-button>

            <div v-if="this.$slots.title" class="text-lg">
                <slot name="title"></slot>
            </div>

            <slot name="content"></slot>
        </div>

        <div v-if="this.$slots.footer" class="px-6 py-4 bg-gray-100 text-right">
            <slot name="footer"></slot>
        </div>
    </modal>
</template>

<script>
import EmButton from './Button';
import Modal from './Modal';

export default {
    components: {
        EmButton,
        Modal,
    },

    props: {
        show: {
            default: false,
        },
        maxWidth: {
            default: '4xl',
        },
        closeable: {
            default: true,
        },
        id: {
            default: null,
        },
        position: {
            default: 'fixed',
        },
    },

    methods: {
        closeModal() {
            this.$root.$emit('closeModal', [this.id]);
        },
    },
};
</script>
