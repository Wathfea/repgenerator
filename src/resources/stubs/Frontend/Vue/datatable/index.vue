<template>
    <Table route="users" :set-columns="setColumns">
        <template #cell(roles)="{ value, item }">
            <span v-for="role in value" class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-500 text-white">{{ role.name }}</span>
        </template>
    </Table>
</template>

<script setup>
import Table from "../DataTable/Table";
import useModel from "~/components/model";
const { getModels }  = useModel('roles', false);
const setColumns = {
    'id': {
        width: '120px',
        name: 'ID',
        isSorting: true
    },
    'last_name' : 'Vezetéknév',
    'first_name': 'Keresztnév',
    'email': 'E-mail cím',
    'company_name': 'Cég név',
    'tax_number': {
        width: '180px',
        name: 'Adószám',
    },
    'phone_number': 'Telefonszám',
    'contact_name': 'Kapcsolattartó',
    'roles': {
        valuesGetter: getModels,
        column: 'id',
        name: 'Szerep',
        width: '400px'
    },
    'created_at': {
        name: 'Létrehozva',
        isDate: true
    }
}
</script>
