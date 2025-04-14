<template>
    <div id="app">
      <router-view />
    </div>
  </template>
  
  <style>
  body, html {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Helvetica Neue', Helvetica, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', Arial, sans-serif;
  }
  
  #app {
    height: 100%;
  }
  
  .el-table th {
    background-color: #f5f7fa !important;
  }
  </style>