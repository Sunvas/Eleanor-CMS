<ul class="pagination mb-0" v-if="pages>1">
	<li class="page-item"><a class="page-link" :class="{active:page==1}" :href="Page(1)">1</a></li>
	<li class="page-item" v-if="page==4"><a class="page-link" :href="Page(2)">2</a></li>
	<li class="page-item" v-else-if="page>3"><span class="page-link">&hellip;</span></li>
	<li class="page-item" v-if="page>2"><a class="page-link" :href="Page(page-1)" v-text="page-1"></a></li>
	<li class="page-item" v-if="page>1"><a class="page-link active" :href="Page(page)" v-text="page"></a></li>
	<li class="page-item" v-if="pages>page"><a class="page-link" :href="Page(page+1)" v-text="page+1"></a></li>
	<li class="page-item" v-if="page==pages-3"><a class="page-link" :href="Page(pages-1)" v-text="pages-1"></a></li>
	<li class="page-item" v-else-if="pages>page+2"><span class="page-link">&hellip;</span></li>
	<li class="page-item" v-if="pages>page+1"><a class="page-link" :href="Page(pages)" v-text="pages"></a></li>
</ul>
