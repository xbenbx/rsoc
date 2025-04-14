<template>
    <div class="dashboard-container">
      <!-- 统计卡片 -->
      <el-row :gutter="20">
        <el-col :span="6" v-for="(item, index) in statCards" :key="index">
          <el-card shadow="hover" class="stat-card">
            <div class="stat-icon" :style="{ backgroundColor: item.color }">
              <i :class="item.icon"></i>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ dashboardData[item.key] || 0 }}</div>
              <div class="stat-title">{{ item.title }}</div>
            </div>
          </el-card>
        </el-col>
      </el-row>
  
      <!-- 图表区域 -->
      <el-row :gutter="20" style="margin-top: 20px;">
        <el-col :span="16">
          <el-card shadow="hover">
            <div slot="header" class="clearfix">
              <span>RSOC点击趋势</span>
              <el-date-picker
                v-model="dateRange"
                type="daterange"
                range-separator="至"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                size="mini"
                style="float: right; width: 250px"
                @change="handleDateRangeChange"
              ></el-date-picker>
            </div>
            <div class="chart-container">