<script>
    import injection from '../helpers/injection';

    export default {
        beforeRouteEnter(to, from, next) {
            injection.loading.start();
            injection.http.post(`${window.api}/member/administration/information/rule`).then(response => {
                next(vm => {
                    vm.form.informations = response.data.data.informations;
                    vm.groups = response.data.data.extras;
                    injection.loading.finish();
                });
            }).catch(() => {
                injection.loading.error();
            });
        },
        data() {
            return {
                action: `${window.api}/member/administration/upload`,
                form: {
                    activate: 'no',
                    email: '',
                    id: 0,
                    informations: [],
                    name: '',
                    password: '',
                },
                groups: [],
                loading: false,
                sex: [
                    {
                        label: '保密',
                        value: '0',
                    },
                    {
                        label: '男',
                        value: '1',
                    },
                    {
                        label: '女',
                        value: '2',
                    },
                ],
                rules: {
                    email: [
                        {
                            required: true,
                            type: 'email',
                            message: '请输入正确的电子邮箱',
                            trigger: 'change',
                        },
                        {
                            required: true,
                            type: 'string',
                            message: '请输入用户名',
                            trigger: 'change',
                        },
                    ],
                    name: [
                        {
                            required: true,
                            type: 'string',
                            message: '请输入用户名',
                            trigger: 'change',
                        },
                    ],
                    password: [
                        {
                            required: true,
                            type: 'string',
                            message: '请输入密码',
                            trigger: 'change',
                        },
                    ],
                },
                trans: injection.trans,
            };
        },
        methods: {
            submit() {
                const self = this;
                self.loading = true;
                self.$refs.form.validate(valid => {
                    if (valid) {
                        const data = self.form;
                        if (self.form.sex === '男') {
                            data.sex = 1;
                        } else if (self.form.sex === '女') {
                            data.sex = 2;
                        } else {
                            data.sex = 0;
                        }
                        self.$http.post(`${window.api}/member/administration/user/create`, data).then(() => {
                            self.$notice.open({
                                title: '添加用户成功！',
                            });
                            self.$router.push('/member/user');
                        }).finally(() => {
                            self.loading = false;
                        });
                    } else {
                        self.$notice.error({
                            title: '请正确填写用户信息',
                        });
                        self.loading = false;
                    }
                });
            },
            uploadBefore() {
                injection.loading.start();
            },
            uploadError(error, data) {
                const self = this;
                injection.loading.error();
                if (typeof data.message === 'object') {
                    for (const p in data.message) {
                        self.$notice.error({
                            title: data.message[p],
                        });
                    }
                } else {
                    self.$notice.error({
                        title: data.message,
                    });
                }
            },
            uploadFormatError(file) {
                this.$notice.warning({
                    title: '文件格式不正确',
                    desc: `文件 ${file.name} 格式不正确，请上传 jpg 或 png 格式的图片。`,
                });
            },
            uploadSuccess(data) {
                const self = this;
                injection.loading.finish();
                self.$notice.open({
                    title: data.message,
                });
                self.form.avatar = data.data.path;
            },
        },
        mounted() {
            this.$store.commit('title', '用户详情 - 用户中心');
        },
    };
</script>
<template>
    <div class="member-warp">
        <div class="user-create">
            <card :bordered="false">
                <p slot="title">用户详情</p>
                <i-form :label-width="200" :model="form" ref="form" :rules="rules">
                    <p class="extend-title">基本资料</p>
                    <row>
                        <i-col span="12">
                            <form-item label="用户名" prop="name">
                                <i-input placeholder="请输入用户名" v-model="form.name"></i-input>
                            </form-item>
                        </i-col>
                    </row>
                    <row>
                        <i-col span="12">
                            <form-item label="头像" prop="avatar">
                                <div class="image-preview" v-if="form.avatar">
                                    <img :src="form.avatar">
                                    <icon type="close" @click.native="removeAvatar"></icon>
                                </div>
                                <upload :action="action"
                                        :format="['jpg','jpeg','png']"
                                        :headers="{
                                            Authorization: `Bearer ${$store.state.token.access_token}`
                                        }"
                                        :max-size="2048"
                                        :on-error="uploadError"
                                        :on-format-error="uploadFormatError"
                                        :on-success="uploadSuccess"
                                        ref="upload"
                                        :show-upload-list="false"
                                        v-if="form.avatar === '' || form.avatar === null">
                                </upload>
                            </form-item>
                        </i-col>
                    </row>
                    <row>
                        <i-col span="12">
                            <form-item label="密码" prop="name">
                                <i-input placeholder="请输入密码" v-model="form.password"></i-input>
                            </form-item>
                        </i-col>
                    </row>
                    <row>
                        <i-col span="12">
                            <form-item label="E-mail" prop="email">
                                <i-input placeholder="请输入电子邮件" v-model="form.email"></i-input>
                            </form-item>
                        </i-col>
                    </row>
                    <row>
                        <i-col span="12">
                            <form-item label="激活状态">
                                <radio-group v-model="form.activate" size="large">
                                    <radio label="yes">
                                        <span>已激活</span>
                                    </radio>
                                    <radio label="no">
                                        <span>未激活</span>
                                    </radio>
                                </radio-group>
                            </form-item>
                        </i-col>
                    </row>
                    <template v-for="group in groups">
                        <p class="extend-title">{{ group.name }}</p>
                        <row v-for="information in group.informations">
                            <i-col span="12">
                                <form-item :label="information.name" :key="information.id" :prop="'informations.' + information.id + '.value'" :rules="form.informations[information.id].rules">
                                    <i-input v-if="information.type === 'input'" v-model="form.informations[information.id].value"></i-input>
                                    <i-input :rows="4" type="textarea"
                                             v-if="information.type === 'textarea'"
                                             v-model="form.informations[information.id].value"></i-input>
                                    <date-picker :type="information.type"
                                                 v-if="information.type === 'date' ||
                                                       information.type === 'daterange' ||
                                                       information.type === 'datetime'"
                                                 v-model="form.informations[information.id].value"></date-picker>
                                    <radio-group v-model="form.informations[information.id].value" size="large" v-if="information.type === 'radio'">
                                        <radio :label="option" v-for="option in information.opinions"></radio>
                                    </radio-group>
                                    <p>{{ information.description }}</p>
                                </form-item>
                            </i-col>
                        </row>
                    </template>
                    <row>
                        <i-col span="12">
                            <form-item>
                                <i-button :loading="loading" type="primary" @click.native="submit">
                                    <span v-if="!loading">确认提交</span>
                                    <span v-else>正在提交…</span>
                                </i-button>
                            </form-item>
                        </i-col>
                    </row>
                </i-form>
            </card>
        </div>
    </div>
</template>