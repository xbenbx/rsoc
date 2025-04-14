<template>
    <div class="login-container">
      <el-card class="login-card">
        <div class="title-container">
          <h3 class="title">ADS.COM RSOC管理系统</h3>
        </div>
        
        <el-form ref="loginForm" :model="loginForm" :rules="loginRules" class="login-form" auto-complete="on" label-position="left">
          <el-form-item prop="username">
            <el-input
              ref="username"
              v-model="loginForm.username"
              placeholder="用户名"
              name="username"
              type="text"
              tabindex="1"
              auto-complete="on"
              prefix-icon="el-icon-user"
            />
          </el-form-item>
  
          <el-form-item prop="password">
            <el-input
              :key="passwordType"
              ref="password"
              v-model="loginForm.password"
              :type="passwordType"
              placeholder="密码"
              name="password"
              tabindex="2"
              auto-complete="on"
              @keyup.enter.native="handleLogin"
              prefix-icon="el-icon-lock"
            />
          </el-form-item>
  
          <el-button :loading="loading" type="primary" style="width:100%;margin-bottom:30px;" @click.native.prevent="handleLogin">登录</el-button>
        </el-form>
      </el-card>
    </div>
  </template>
  
  <script>
  export default {
    name: 'Login',
    data() {
      return {
        loginForm: {
          username: 'admin',
          password: '123456'
        },
        loginRules: {
          username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
          password: [{ required: true, message: '请输入密码', trigger: 'blur' }]
        },
        loading: false,
        passwordType: 'password',
        redirect: undefined
      }
    },
    watch: {
      $route: {
        handler: function(route) {
          this.redirect = route.query && route.query.redirect
        },
        immediate: true
      }
    },
    methods: {
      showPwd() {
        this.passwordType = this.passwordType === 'password' ? '' : 'password'
        this.$nextTick(() => {
          this.$refs.password.focus()
        })
      },
      handleLogin() {
        this.$refs.loginForm.validate(valid => {
          if (valid) {
            this.loading = true
            this.$store.dispatch('user/login', this.loginForm).then(() => {
              this.$router.push({ path: this.redirect || '/' })
              this.loading = false
            }).catch(() => {
              this.loading = false
            })
          } else {
            console.log('error submit!!')
            return false
          }
        })
      }
    }
  }
  </script>
  
  <style lang="scss" scoped>
  .login-container {
    min-height: 100%;
    width: 100%;
    background-color: #2d3a4b;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
  
    .login-card {
      width: 520px;
      padding: 35px 35px 15px 35px;
      background: #fff;
      border-radius: 10px;
    }
  
    .title-container {
      position: relative;
      text-align: center;
  
      .title {
        font-size: 26px;
        color: #333;
        margin: 0 auto 30px auto;
        font-weight: bold;
      }
    }
  }
  </style>