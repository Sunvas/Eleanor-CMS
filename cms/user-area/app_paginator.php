<div class="numbers" v-if="pages>1">
	<a :class="{active:page==1}" :href="Page(1)">1</a>
	<a v-if="page==4" :href="Page(2)">2</a>
	<span v-else-if="page>3" role="button" @click="InputPage">&hellip;</span>
	<a v-if="page>2" :href="Page(page-1)" v-text="page-1"></a>
	<a v-if="page>1" class="active" :href="Page(page)" v-text="page"></a>
	<a v-if="pages>page" :href="Page(page+1)" v-text="page+1"></a>
	<a v-if="page==pages-3" :href="Page(pages-1)" v-text="pages-1"></a>
	<span v-else-if="pages>page+2" role="button" @click="InputPage">&hellip;</span>
	<a v-if="pages>page+1" :href="Page(pages)" v-text="pages"></a>
</div>