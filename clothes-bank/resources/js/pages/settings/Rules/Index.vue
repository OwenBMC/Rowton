<script setup lang="ts">

import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: AppLayout,
});


interface Condition {
    attribute: string;
    operator: string;
    value: string;
}


interface Group {
    operator: 'AND' | 'OR';
    conditions: Condition[];
}


interface Rule {
    id: number;
    name: string;
    service_category_id: number;

    service_item?: {
        name:string;
        service_category_id: number;
    };

    category?: {
        name:string;
    };

    groups: Group[];
}


const props = defineProps<{
    rules: Rule[];
    services:any[];
    categories:any[];
    attributes:any;
}>();



const form = ref({

    name: '',

    service_item_id: null,

    service_category_id: null,


    groups: [

        {
            operator:'AND',

            conditions:[
                {
                    attribute:'',
                    operator:'',
                    value:'',
                }
            ]
        }

    ]

});



function newCondition():Condition
{
    return {
        attribute:'',
        operator:'',
        value:'',
    };
}



function newGroup():Group
{
    return {

        operator:'AND',

        conditions:[
            newCondition()
        ]

    };
}



function addGroup()
{
    form.value.groups.push(
        newGroup()
    );
}



function removeGroup(index:number)
{
    form.value.groups.splice(index,1);
}



function addCondition(group:Group)
{
    group.conditions.push(
        newCondition()
    );
}



function removeCondition(
    group:Group,
    index:number
)
{
    group.conditions.splice(index,1);
}




function operators(condition:Condition)
{
    return props.attributes[
        condition.attribute
    ]?.operators ?? [];
}




function values(condition:Condition)
{
    return props.attributes[
        condition.attribute
    ]?.values ?? [];
}




function save()
{
    router.post(
        '/admin/settings/rules',
        form.value,
        {

            onSuccess(){

                form.value = {

                    name:'',

                    service_item_id:null,

                    service_category_id:null,

                    groups:[
                        newGroup()
                    ]

                };

            }

        }
    );
}




function remove(rule:Rule)
{
    if(confirm(`Delete ${rule.name}?`)){

        router.delete(
            `/admin/settings/rules/${rule.id}`
        );

    }
}


</script>



<template>

<div class="p-6 max-w-6xl">


<h1 class="text-2xl font-semibold mb-2">
    Eligibility Rules
</h1>


<p class="text-gray-600 mb-6">
    Configure who can receive services.
</p>



<div class="border rounded-lg p-5 mb-8">


<h2 class="font-semibold mb-4">
    Create rule
</h2>



<input
    v-model="form.name"
    placeholder="Rule name"
    class="border rounded px-3 py-2 w-full mb-4"
/>



<div class="grid md:grid-cols-2 gap-3 mb-5">


<select
    v-model="form.service_category_id"
    class="border rounded px-3 py-2"
>

<option :value="null">
    All categories
</option>

<option
    v-for="category in categories"
    :key="category.id"
    :value="category.id"
>
    {{category.name}}
</option>

</select>



<select
    v-model="form.service_item_id"
    class="border rounded px-3 py-2"
>

<option :value="null">
    All services
</option>

<option
    v-for="service in services"
    :key="service.id"
    :value="service.id"
>
    {{service.name}}
</option>

</select>


</div>





<div
    v-for="(group,gIndex) in form.groups"
    :key="gIndex"
    class="border rounded p-4 mb-4"
>


<div class="flex justify-between mb-3">


<select
    v-model="group.operator"
    class="border rounded px-3 py-1"
>

<option value="AND">
    All conditions must match (AND)
</option>

<option value="OR">
    Any condition can match (OR)
</option>

</select>



<button
    @click="removeGroup(gIndex)"
    class="text-red-600"
>
    Remove group
</button>


</div>





<div
    v-for="(condition,cIndex) in group.conditions"
    :key="cIndex"
    class="flex gap-2 mb-2"
>



<select
    v-model="condition.attribute"
    class="border rounded px-2 py-1"
>

<option value="">
    Attribute
</option>

<option
    v-for="(attribute,key) in attributes"
    :key="key"
    :value="key"
>
    {{attribute.label}}
</option>

</select>




<select
    v-model="condition.operator"
    class="border rounded px-2 py-1"
>

<option value="">
    Operator
</option>

<option
    v-for="operator in operators(condition)"
    :key="operator"
    :value="operator"
>
    {{operator}}
</option>

</select>




<select
    v-if="attributes[condition.attribute]?.type === 'enum'"
    v-model="condition.value"
    class="border rounded px-2 py-1"
>

<option value="">
    Value
</option>

<option
    v-for="value in values(condition)"
    :key="value.key"
    :value="value.key"
>
    {{value.label}}
</option>

</select>




<input
    v-else
    v-model="condition.value"
    placeholder="Value"
    class="border rounded px-2 py-1"
/>




<button
    @click="removeCondition(group,cIndex)"
    class="text-red-600"
>
    X
</button>


</div>



<button
    @click="addCondition(group)"
    class="border rounded px-3 py-1"
>
    + Condition
</button>



</div>





<button
    @click="addGroup"
    class="border rounded px-3 py-1 mr-2"
>
    + Condition Group
</button>



<button
    @click="save"
    class="border rounded px-3 py-1"
>
    Save Rule
</button>


</div>





<div class="border rounded-lg overflow-hidden">


<table class="w-full">


<thead class="bg-gray-50">

<tr>

<th class="p-3 text-left">
Rule
</th>

<th>
Service
</th>

<th>
Conditions
</th>

<th>
</th>

</tr>

</thead>




<tbody>


<tr
    v-for="rule in rules"
    :key="rule.id"
    class="border-t"
>


<td class="p-3">
{{rule.name}}
</td>
<td class="text-center">
    <!-- {{ JSON.stringify(rule) }} -->

{{categories.find(cat => cat.id == rule.service_category_id || cat.id == rule.service_item?.service_category_id )?.name ?? rule.service_item?.service_category_id}}

</td>

<td class="text-center">

{{rule.service_item?.name ?? rule.category?.name ?? 'All services'}}

</td>




<td>


<div
    v-for="(group,index) in rule.groups"
    :key="index"
    class="mb-2"
>

<strong>
{{group.operator}}
</strong>


<ul>

<li
    v-for="(condition,i) in group.conditions"
    :key="i"
>
{{condition.attribute}}
{{condition.operator}}
{{condition.value}}
</li>

</ul>


</div>


</td>




<td>

<button
@click="remove(rule)"
class="text-red-600"
>
Delete
</button>

</td>


</tr>


</tbody>


</table>


</div>


</div>

</template>