<template>
    <div>
        <jet-banner/>

        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-0 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="pt-4 flex-shrink-0 flex items-center">
                                <inertia-link :href="'/'">
                                    <em-application-mark :class="'h-9'"/>
                                </inertia-link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden pl-6 space-x-8 sm:-my-px sm:flex sm:items-center">
                                <em-nav-link v-for="view in $page.props.views"
                                             :class="'h-9'"
                                             :key="view"
                                             :href="'/' + view.pluralize()"
                                             :active="($page.props.currentRouteName === 'gets.' + view)
                                                || ($page.props.currentRouteName === 'gets.' + view.pluralize())">
                                    {{ __(view.ucFirst().pluralize()) }}
                                </em-nav-link>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative">
                                <!-- Teams Dropdown -->
                                <jet-dropdown
                                    align="right"
                                    width="60"
                                    v-if="$page.props.jetstream.hasTeamFeatures && $page.props.isAdmin">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md bg-white hover:bg-gray-50 hover:text-indigo-600 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ __($page.props.user.current_team.name.ucFirst().pluralize()) }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                     viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                          d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                          clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    {{ __('Manage Team') }}
                                                </div>

                                                <!-- Team Settings -->
                                                <em-dropdown-link
                                                    :href="route('teams.show', $page.props.user.current_team)">
                                                    {{ __('Team Settings') }}
                                                </em-dropdown-link>

                                                <em-dropdown-link :href="route('teams.create')"
                                                                  v-if="$page.props.jetstream.canCreateTeams">
                                                    {{ __('Create New Team') }}
                                                </em-dropdown-link>

                                                <div class="border-t border-gray-100"></div>

                                                <!-- Team Switcher -->
                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    {{ __('Switch Teams') }}
                                                </div>

                                                <template v-for="team in $page.props.user.all_teams">
                                                    <form @submit.prevent="switchToTeam(team)" :key="team.id">
                                                        <em-dropdown-link as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id === $page.props.user.current_team_id"
                                                                     class="mr-2 h-5 w-5 text-green-400" fill="none"
                                                                     stroke-linecap="round" stroke-linejoin="round"
                                                                     stroke-width="2" stroke="currentColor"
                                                                     viewBox="0 0 24 24">
                                                                    <path
                                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                                </svg>
                                                                <div>{{ __(team.name.ucFirst().pluralize()) }}</div>
                                                            </div>
                                                        </em-dropdown-link>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </jet-dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <jet-dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos"
                                                class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition duration-150 ease-in-out">
                                            <img class="h-8 w-8 rounded-full object-cover"
                                                 :src="$page.props.user.profile_photo_url"
                                                 :alt="$page.props.user.name"/>
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                                {{ $page.props.user.name }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                     viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                          clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            {{ __('Manage Account') }}
                                        </div>

                                        <em-dropdown-link :href="route('profile.show')">
                                            {{ __('Profile') }}
                                        </em-dropdown-link>

                                        <em-dropdown-link :href="route('api-tokens.index')"
                                                          v-if="$page.props.jetstream.hasApiFeatures">
                                            {{ __('API Tokens') }}
                                        </em-dropdown-link>

                                        <div class="border-t border-gray-100"></div>

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <em-dropdown-link as="button">
                                                {{ __('Logout') }}
                                            </em-dropdown-link>
                                        </form>
                                    </template>
                                </jet-dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = ! showingNavigationDropdown"
                                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"/>
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}"
                     class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <em-responsive-nav-link v-for="view in $page.props.views"
                                                :key="view"
                                                :href="'/' + view.pluralize()"
                                                :active="($page.props.currentRouteName === 'gets.' + view)
                                                    || ($page.props.currentRouteName === 'gets.' + view.pluralize())">
                            {{ __(view.ucFirst().pluralize()) }}
                        </em-responsive-nav-link>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="flex-shrink-0 mr-3">
                                <img class="h-10 w-10 rounded-full object-cover"
                                     :src="$page.props.user.profile_photo_url" :alt="$page.props.user.name"/>
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">{{ $page.props.user.name }}</div>
                                <div class="font-medium text-sm text-gray-500">{{ $page.props.user.email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <em-responsive-nav-link :href="route('profile.show')"
                                                    :active="route().current('profile.show')">
                                {{ __('Profile') }}
                            </em-responsive-nav-link>

                            <em-responsive-nav-link :href="route('api-tokens.index')"
                                                    :active="route().current('api-tokens.index')"
                                                    v-if="$page.props.jetstream.hasApiFeatures">
                                {{ __('API Tokens') }}
                            </em-responsive-nav-link>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <em-responsive-nav-link as="button">
                                    {{ __('Logout') }}
                                </em-responsive-nav-link>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200"></div>

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('Manage Team') }}
                                </div>

                                <!-- Team Settings -->
                                <em-responsive-nav-link :href="route('teams.show', $page.props.user.current_team)"
                                                        :active="route().current('teams.show')">
                                    {{ __('Team Settings') }}
                                </em-responsive-nav-link>

                                <em-responsive-nav-link :href="route('teams.create')"
                                                        :active="route().current('teams.create')">
                                    {{ __('Create New Team') }}
                                </em-responsive-nav-link>

                                <div class="border-t border-gray-200"></div>

                                <!-- Team Switcher -->
                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    {{ __('Switch Teams') }}
                                </div>

                                <template v-for="team in $page.props.user.all_teams">
                                    <form @submit.prevent="switchToTeam(team)" :key="team.id">
                                        <em-responsive-nav-link as="button">
                                            <div class="flex items-center">
                                                <svg v-if="team.id === $page.props.user.current_team_id"
                                                     class="mr-2 h-5 w-5 text-green-400" fill="none"
                                                     stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                     stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <div>{{ team.name }}</div>
                                            </div>
                                        </em-responsive-nav-link>
                                    </form>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-2 px-4 sm:px-6 lg:px-8">
                    <slot name="header"></slot>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot></slot>
            </main>

            <!-- Modal Portal -->
            <portal-target name="modal" multiple>
            </portal-target>
        </div>
    </div>
</template>

<script>
import EmApplicationMark from './ApplicationMark';
import JetBanner from '@/Jetstream/Banner';
import JetDropdown from '@/Jetstream/Dropdown';
import EmDropdownLink from './DropdownLink';
import EmNavLink from './NavLink';
import EmResponsiveNavLink from './ResponsiveNavLink';

export default {
    components: {
        EmApplicationMark,
        JetBanner,
        JetDropdown,
        EmDropdownLink,
        EmNavLink,
        EmResponsiveNavLink,
    },

    provide: {
        filterFieldDefaultClass: 'm-1 p-1 rounded cursor-pointer bg-indigo-50 hover:bg-indigo-500 hover:text-white text-sm',
        filterFieldIsChecked: 'bg-indigo-400 text-white',
        paginationActive: 'bg-indigo-400 text-white rounded-md',
        paginationNull: 'bg-white text-gray-500',
        leftColumn: 'table-cell align-middle text-right p-1 w-96',
        rightColumn: 'table-cell align-middle text-left p-1',
        warningClass: 'text-red-600',
        labelDefaultClass: 'text-sm',
        inputDefaultClass: 'rounded-md shadow-sm max-w-xs',
        textareaDefaultClass: 'w-96 h-20',
        fieldWarningClass: 'rounded-md shadow-sm max-w-xs border border-red-600',
        pDefaultClass: 'text-xs',
    },

    data() {
        return {
            showingNavigationDropdown: false,
        };
    },

    methods: {
        switchToTeam(team) {
            this.$inertia.put('/current-team', {
                'team_id': team.id,
            }, {
                preserveState: false,
            });
        },

        logout() {
            axios.post('/logout').then(response => {
                window.location = '/';
            });
        },
    },

    computed: {
        path() {
            return window.location.pathname;
        },
    },
};
</script>
