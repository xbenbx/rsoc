<template>
    <el-container class="app-wrapper">
      <el-aside width="auto" class="sidebar-container">
        <!-- Logo -->
        <div class="logo-container">
          <router-link to="/">
            <div class="logo-wrapper">
              <img src="@/assets/logo.png" class="logo">
              <h1 class="title" v-if="!sidebarCollapsed">RSOC管理系统</h1>
            </div>
          </router-link>
        </div>
        
        <!-- 侧边栏菜单 -->
        <el-menu
          :default-active="activeMenu"
          :collapse="sidebarCollapsed"
          :collapse-transition="false"
          background-color="#304156"
          text-color="#bfcbd9"
          active-text-color="#409EFF"
          router
        >
          <sidebar-item
            v-for="route in routes"
            :key="route.path"
            :item="route"
            :base-path="route.path"
          />
        </el-menu>
      </el-aside>
      
      <el-container class="main-container">
        <el-header class="app-header">
          <!-- 折叠按钮 -->
          <div class="hamburger-container" @click="toggleSidebar">
            <i :class="sidebarCollapsed ? 'el-icon-s-unfold' : 'el-icon-s-fold'"></i>
          </div>
          
          <!-- 面包屑导航 -->
          <breadcrumb class="breadcrumb-container" />
          
          <!-- 用户信息 -->
          <div class="right-menu">
            <el-dropdown trigger="click" @command="handleCommand">
              <div class="avatar-wrapper">
                <img src="@/assets/avatar.png" class="user-avatar">
                <span class="username">{{ name }}</span>
                <i class="el-icon-caret-bottom"></i>
              </div>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="profile">个人信息</el-dropdown-item>
                <el-dropdown-item command="logout" divided>退出登录</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </div>
        </el-header>
        
        <el-main class="app-main">
          <transition name="fade-transform" mode="out-in">
            <router-view :key="key" />
          </transition>
        </el-main>
      </el-container>
    </el-container>
  </template>
  
  <script>
  import { mapGetters } from 'vuex'
  import SidebarItem from '@/components/SidebarItem'
  import Breadcrumb from '@/components/Breadcrumb'
  
  export default {
    name: 'Layout',
    components: {
      SidebarItem,
      Breadcrumb
    },
    computed: {
      ...mapGetters([
        'sidebarCollapsed',
        'name',
        'avatar',
        'roles'
      ]),
      key() {
        return this.$route.path
      },
      routes() {
        return this.$router.options.routes[1].children.filter(route => !route.hidden)
      },
      activeMenu() {
        const { path } = this.$route
        return path
      }
    },
    methods: {
      toggleSidebar() {
        this.$store.dispatch('toggleSidebar')
      },
      handleCommand(command) {
        if (command === 'logout') {
          this.$store.dispatch('user/logout').then(() => {
            this.$router.push('/login')
          })
        } else if (command === 'profile') {
          this.$router.push('/profile')
        }
      }
    }
  }
  </script>
  
  <style lang="scss" scoped>
  .app-wrapper {
    height: 100%;
    width: 100%;
    position: relative;
    
    .sidebar-container {
      transition: width 0.28s;
      width: 210px !important;
      background-color: #304156;
      height: 100%;
      z-index: 1001;
      overflow: hidden;
      
      // 隐藏水平滚动条
      &::-webkit-scrollbar {
        display: none;
      }
      
      .logo-container {
        height: 60px;
        padding: 10px 0;
        text-align: center;
        overflow: hidden;
        
        .logo-wrapper {
          height: 100%;
          width: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          
          .logo {
            width: 32px;
            height: 32px;
            margin-right: 10px;
          }
          
          .title {
            display: inline-block;
            margin: 0;
            color: #fff;
            font-weight: 600;
            line-height: 50px;
            font-size: 14px;
          }
        }
      }
    }
    
    .main-container {
      height: 100%;
      transition: margin-left .28s;
      margin-left: 210px;
      position: relative;
      
      .app-header {
        background-color: #fff;
        box-shadow: 0 1px 4px rgba(0,21,41,.08);
        display: flex;
        align-items: center;
        padding: 0;
        height: 60px;
        box-sizing: border-box;
        position: relative;
        
        .hamburger-container {
          line-height: 60px;
          height: 100%;
          float: left;
          cursor: pointer;
          transition: background .3s;
          padding: 0 15px;
          
          &:hover {
            background: rgba(0, 0, 0, .025);
          }
          
          i {
            font-size: 20px;
            color: #666;
          }
        }
        
        .breadcrumb-container {
          float: left;
        }
        
        .right-menu {
          float: right;
          height: 100%;
          line-height: 60px;
          margin-right: 10px;
          
          .avatar-wrapper {
            margin-top: 5px;
            position: relative;
            display: flex;
            align-items: center;
            padding: 0 8px;
            height: 50px;
            cursor: pointer;
            
            .user-avatar {
              width: 30px;
              height: 30px;
              border-radius: 15px;
              margin-right: 8px;
            }
            
            .username {
              font-size: 14px;
              color: #666;
            }
            
            .el-icon-caret-bottom {
              font-size: 12px;
              margin-left: 5px;
            }
          }
        }
      }
      
      .app-main {
        /*50 = navbar*/
        min-height: calc(100vh - 60px);
        padding: 20px;
        position: relative;
        overflow: auto;
        box-sizing: border-box;
      }
    }
  }
  </style>