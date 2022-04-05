<div class="form-group mb-3">
    <label class="control-label">Title</label>
    <input type="text" name="title" value="{{ Arr::get($attributes, 'title') }}" class="form-control" />
</div>
<div class="form-group mb-3">
    <label class="control-label">Filter By</label>
    <select name="filter_by" class="form-control" id="filterBy">
        <option value="featured" @if (Arr::get($attributes, 'filter_by') == 'featured') selected @endif>Featured Posts</option>
        <option value="recent" @if (Arr::get($attributes, 'filter_by') == 'recent') selected @endif>Recent Posts</option>
        <option value="ids"  @if (Arr::get($attributes, 'filter_by') == 'ids') selected @endif>Post Ids</option>
    </select>
</div>
<div class="tab-filter tab-featured">
    <div class="form-group mb-3">
        <label class="control-label">Limit</label>
        <input type="number" name="limit" value="{{ Arr::get($attributes, 'limit', 4) }}" class="form-control" />
    </div>
</div>
<div class="tab-filter tab-recent">
    <div class="form-group mb-3">
        <label class="control-label">Limit</label>
        <input type="number" name="limit" value="{{ Arr::get($attributes, 'limit', 4) }}" class="form-control" />
    </div>
</div>
<div class="tab-filter tab-ids">
    <div class="form-group mb-3">
        <label class="control-label">Post Ids</label>
        <input name="include" value="{{ Arr::get($attributes, 'include') }}" class="form-control" placeholder="1,2,3">
    </div>
</div>

<script>
    'use strict';
    var filterByValue = $( '#filterBy option' ).filter(':selected').val();
    $('#filterBy').on('change', function () {
        triggerTab($(this).val());
    });
    function triggerTab(tabName) {
        $('.tab-filter').hide();
        $('.tab-filter').find('input').attr('disabled', true);
        $('.tab-' + tabName).show();
        $('.tab-' + tabName).find('input').attr('disabled', false).show();
    }
    triggerTab(filterByValue);
</script>
