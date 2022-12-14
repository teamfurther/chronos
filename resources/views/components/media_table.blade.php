<script type="text/x-template" id="media-table-template">
    <div id="media-table-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="filter-bar pull-left">
                        <div class="search">
                            <div class="input-group">
                                <span class="input-group-addon"><span class="c4icon-2x c4icon-search-2"></span></span>
                                <input class="form-control" type="text" placeholder="{!! trans('chronos::interface.Search') !!}" v-on:keyup="search" v-model="filters.search" />
                                <span class="input-group-addon reset" v-on:click="clearSearch"><span class="c4icon-lg c4icon-cross-2"></span></span>
                            </div>
                        </div>
                    </div>
                    @if (Auth::user()->can('upload_media') && Route::currentRouteName() !== 'chronos.content.media')
                        <div class="main-action create marginT15 pull-right">
                            <ajax-upload action="{{ route('api.content.media.store') }}" v-bind:multiple="true">
                                <a data-placement="left" data-tooltip="tooltip" title="{!! trans('chronos::interface.Upload media') !!}">{!! trans('chronos::interface.Upload media') !!}</a>
                            </ajax-upload>
                        </div>
                    @endif
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel">
                        <h2 class="panel-title">{!! trans('chronos::interface.Available files') !!}</h2>

                        <div class="media-table">
                            <ul class="media-list" v-if="!dataLoader && data.length > 0">
                                <li v-bind:class="{ active: selected.indexOf(key) !== -1 }" v-for="(item, key) in data" v-on:click="select(key, $event)">
                                    <img v-bind:src="item.thumb" v-bind:alt="item.basename" v-if="item.thumb" />
                                    <span class="icon c4icon-5x c4icon-file-2" v-else></span>
                                    <span class="media-title" v-html="item.basename"></span>
                                    <a class="media-delete" data-toggle="modal" data-target="#delete-file-dialog" v-on:click="setdeleteURL(data[lastSelected].endpoints.destroy, $event)"></a>
                                </li>
                            </ul>
                            <div class="media-overview" v-if="!dataLoader && selected.length == 1">
                                <h4>{!! trans('chronos::interface.File overview') !!}</h4>
                                <img v-bind:src="data[lastSelected].thumb" v-bind:alt="data[lastSelected].basename" v-if="data[lastSelected].thumb" />
                                <span class="icon c4icon-5x c4icon-file-2" v-else></span>
                                <table class="table table-condensed">
                                    <tr>
                                        <td><strong v-html="data[lastSelected].filename"></strong></td>
                                    </tr>
                                    <tr>
                                        <td>ID: <span v-html="data[lastSelected].id"></span> <code>[media id="<span v-html="data[lastSelected].id"></span>"]</code></td>
                                    </tr>
                                    <tr>
                                        <td v-html="data[lastSelected].created_at"></td>
                                    </tr>
                                    <tr>
                                        <td v-html="data[lastSelected].sizeFormatted"></td>
                                    </tr>
                                    <tr v-if="data[lastSelected].is_image && data[lastSelected].image_width && data[lastSelected].image_height">
                                        <td v-html="data[lastSelected].image_width + ' ?? ' + data[lastSelected].image_height"></td>
                                    </tr>
                                </table>

                                <div class="media-attributes">
                                    <div class="row">
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="alt">{!! trans('chronos::forms.Alt tag') !!}</label>
                                                <input class="form-control" id="alt" name="alt" type="text" v-model="data[lastSelected].alt" v-on:blur="saveMediaData(lastSelected)" />
                                            </div>
                                        </div>
                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="title">{!! trans('chronos::forms.Title tag') !!}</label>
                                                <input class="form-control" id="title" name="title" type="text" v-model="data[lastSelected].title" v-on:blur="saveMediaData(lastSelected)" />
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <br />
                                            <span class="loader-small marginB0 marginT5" v-show="savingMediaData"></span>
                                        </div>
                                    </div>
                                </div>

                                <a class="marginR15" v-bind:href="data[lastSelected].file" target="_blank">{!! trans('chronos::interface.Download file') !!}</a>
                                @can ('delete_media')
                                <a class="text-danger" data-toggle="modal" data-target="#delete-file-dialog" v-on:click="setdeleteURL(data[lastSelected].endpoints.destroy, $event)">{!! trans('chronos::interface.Delete file') !!}</a><br />
                                @endcan
                                <a class="btn btn-action marginT15" v-on:click="selectFile(data[lastSelected])" v-if="selectable">{!! trans('chronos::interface.Select file') !!}</a>
                            </div>
                            <div class="media-overview" v-if="!dataLoader && selected.length > 1">
                                <h4><span v-html="selected.length"></span> {!! trans('chronos::interface.files selected') !!}</h4>

                                <span class="icon c4icon-5x c4icon-files-2"></span>

                                @can ('delete_media')
                                <a class="display-block text-danger" data-toggle="modal" data-target="#delete-files-dialog" v-on:click="setdeleteURL('', $event)">{!! trans('chronos::interface.Delete files') !!}</a>
                                @endcan
                            </div>
                        </div>

                        <p class="text-center" v-show="dataLoader"><span class="loader-small"></span></p>
                        <p class="no-results" v-show="!dataLoader && data.length === 0">{!! trans('chronos::interface.There are no results here. Try broadening your search.') !!}</p>
                    </div>

                    @include('chronos::components.pagination')
                </div>
            </div>
        </div>
    </div>
</script>


<script>
    var mediaEventHub = new Vue();

    Vue.component('media-table', {
        created: function() {
            // load data
            if (this.autoload)
                this.getData();

            // add listeners
            mediaEventHub.$on('open-media-dialog', this.openMediaDialog);
            uploadEventHub.$on('upload-finished', this.getData);
            this.$parent.$on('deleted-model-from-dialog', this.deleteMedia);
            this.$parent.$on('perform-bulk-action', this.deleteBulkMedia);
        },
        data: function() {
            return {
                data: [],
                dataLoader: 0,
                deleteURL: null,
                dialog: null,
                dialogData: null,
                filters: {
                    imagesOnly: false,
                    search: ''
                },
                lastSelected: null,
                pagination: {},
                savingMediaData: false,
                searchOn: false,
                selected: []
            }
        },
        methods: {
            clearSearch: function() {
                this.filters.search = '';

                this.searchOn = false;

                this.getData();
            },
            closeMediaDialog: function() {
                if (this.dialog != null) {
                    this.dialog.close();

                    this.dialog = null;
                    this.dialogData = null;
                }
            },
            deleteBulkMedia: function(url, method, arrayName, e) {
                vm.$emit('show-loader');

                // close modal
                if (e) {
                    var target = e.target.closest('.modal');
                    if (target) {
                        var dialog = new Modal(target);
                        dialog.close();
                    }

                    // close dropdown
                    var dropdown = document.querySelector('.bulk-actions');
                    if (dropdown)
                        dropdown.classList.remove('open');
                }

                if (this.selected.length > 1) {
                    var params = {};
                    params[arrayName] = [];
                    this.selected.forEach(function (key) {
                        params[arrayName].push(this.data[key].id);
                    }.bind(this));

                    this.$http({
                        method: method,
                        params: params,
                        url: url
                    }).then(function (response) {
                        vm.$emit('hide-loader');

                        if (response.body.alerts) {
                            response.body.alerts.forEach(function (alert) {
                                vm.$emit('add-alert', alert);
                            }.bind(this));
                        }

                        this.getData();

                        this.selected = [];
                    }, function (response) {
                        vm.$emit('hide-loader');

                        if (response.body.alerts) {
                            response.body.alerts.forEach(function (alert) {
                                vm.$emit('add-alert', alert);
                            }.bind(this));
                        }
                        else {
                            vm.$emit('add-alert', {
                                type: 'error',
                                title: 'AJAX error',
                                message: response.statusText + ' (' + response.status + ')'
                            });
                        }
                    });
                }
            },
            deleteMedia: function(target) {
                vm.$emit('show-loader');

                var dialog = new Modal(target);
                dialog.close();

                this.$http.delete(this.deleteURL).then(function(response) {
                    vm.$emit('hide-loader');

                    if (response.body.alerts) {
                        response.body.alerts.forEach(function(alert) {
                            vm.$emit('add-alert', alert);
                        }.bind(this));
                    }

                    this.getData();
                }, function(response) {
                    vm.$emit('hide-loader');

                    if (response.body.alerts) {
                        response.body.alerts.forEach(function(alert) {
                            vm.$emit('add-alert', alert);
                        }.bind(this));
                    }
                    else {
                        vm.$emit('add-alert', {
                            type: 'error',
                            title: 'AJAX error',
                            message: response.statusText + ' (' + response.status + ')'
                        });
                    }
                });
            },
            getData: function() {
                this.dataLoader++;
                this.lastSelected = null;
                this.selected = [];

                this.$http.get('/api/content/media', {params: {
                    filters: this.filters,
                    perPage: this.pagination.per_page,
                    page: this.pagination.current
                }}).then(function(response) {
                    this.data = response.body.data;

                    // assign pagination vars
                    this.pagination.current = response.body.current_page;
                    this.pagination.last = response.body.last_page;
                    this.pagination.per_page = response.body.per_page;
                    this.pagination.items = response.body.total;

                    this.dataLoader--;
                }, function(response) {
                    this.dataLoader--;

                    if (response.body.alerts) {
                        response.body.alerts.forEach(function(alert) {
                            vm.$emit('add-alert', alert);
                        }.bind(this));
                    }
                    else {
                        vm.$emit('add-alert', {
                            type: 'error',
                            title: 'AJAX error',
                            message: response.statusText + ' (' + response.status + ')'
                        });
                    }
                });
            },
            openMediaDialog: function(imagesOnly, dialogData) {
                this.filters.imagesOnly = imagesOnly;

                var modal = document.getElementById('media-table-wrapper').closest('.modal');
                this.dialog = new Modal(modal);
                this.dialog.open();

                this.dialogData = dialogData;

                this.getData();
            },
            paginate: function(page) {
                this.pagination.current = page;

                this.getData();
            },
            saveMediaData: function(lastSelected) {
                this.savingMediaData = true;

                this.$http.patch('/api/content/media/' + this.data[lastSelected].id, {
                    alt: this.data[lastSelected].alt,
                    title: this.data[lastSelected].title
                }).then(function(response) {
                    this.savingMediaData = false;
                }, function(response) {
                    this.savingMediaData = false;

                    if (response.body.alerts) {
                        response.body.alerts.forEach(function(alert) {
                            vm.$emit('add-alert', alert);
                        }.bind(this));
                    }
                    else {
                        vm.$emit('add-alert', {
                            type: 'error',
                            title: 'AJAX error',
                            message: response.statusText + ' (' + response.status + ')'
                        });
                    }
                });
            },
            search: debounce(function() {
                this.getData();

                this.searchOn = true;
            }, 500),
            select: function(key, event) {
                if (!event.ctrlKey && !event.metaKey)
                    this.selected = [];

                this.lastSelected = key;
                if (this.selected.indexOf(key) == -1)
                    this.selected.push(key);
                else
                    this.selected.splice(this.selected.indexOf(key), 1);
            },
            selectFile: function(file) {
                if (file)
                    mediaEventHub.$emit('select-from-media-dialog', file, this.dialogData);

                this.closeMediaDialog();
            },
            setdeleteURL: function(deleteURL, event) {
                this.deleteURL = deleteURL;

                // open modal - we need this because modal triggers don't work on dynamically created events
                var target = event.target.getAttribute('data-target') && event.target.getAttribute('data-target').replace('#', '');

                var modal = document.getElementById(target);
                var dialog = new Modal(modal);
                dialog.open();
            },
            showAll: function() {
                this.pagination.current = 1;
                this.pagination.per_page = 0;

                this.getData();
            }
        },
        props: {
            autoload: {
                default: true,
                type: Boolean
            },
            selectable: {
                default: false,
                type: Boolean
            }
        },
        template: '#media-table-template'
    });
</script>



@push('content-modals')
    <div class="modal fade" id="delete-file-dialog" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-danger">
                <form v-on:submit.prevent="deleteModelFromDialog">
                    <div class="modal-header">
                        <button type="button" class="modal-close" data-dismiss="modal"><span class="icon c4icon-cross-2"></span></button>
                        <h4 class="modal-title">{!! trans('chronos::interface.Delete file') !!}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="marginT15 text-center"><strong>{!! trans('chronos::interface.WARNING! This action is irreversible.') !!}</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">{!! trans('chronos::interface.Close') !!}</button>
                        <button class="btn btn-danger" name="process" type="submit" value="1">{!! trans('chronos::interface.Delete') !!}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="delete-files-dialog" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content modal-danger">
                <form v-on:submit.prevent="performBulkAction('{{ route('api.content.media.destroy_bulk') }}', 'DELETE', 'media', $event)">
                    <div class="modal-header">
                        <button type="button" class="modal-close" data-dismiss="modal"><span class="icon c4icon-cross-2"></span></button>
                        <h4 class="modal-title">{!! trans('chronos::interface.Delete files') !!}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="marginT15 text-center"><strong>{!! trans('chronos::interface.WARNING! This action is irreversible.') !!}</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" type="button" data-dismiss="modal">{!! trans('chronos::interface.Close') !!}</button>
                        <button class="btn btn-danger" name="process" type="submit" value="1">{!! trans('chronos::interface.Delete') !!}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush