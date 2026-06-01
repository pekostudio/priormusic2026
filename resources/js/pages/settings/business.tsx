import { Form, Head } from '@inertiajs/react';
import BusinessController from '@/actions/App/Http/Controllers/Settings/BusinessController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/business';

type BusinessSettings = {
    company_name: string | null;
    company_code: string | null;
    vat: string | null;
    address: string | null;
    phone: string | null;
    contact_person: string | null;
};

export default function Business({ business }: { business: BusinessSettings }) {
    return (
        <>
            <Head title="Business settings" />

            <h1 className="sr-only">Business settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Business"
                    description="Update company details used for your account"
                />

                <Form
                    {...BusinessController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="max-w-xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="company_name">
                                    Company name
                                </Label>
                                <Input
                                    id="company_name"
                                    name="company_name"
                                    defaultValue={business.company_name ?? ''}
                                    autoComplete="organization"
                                />
                                <InputError message={errors.company_name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="company_code">
                                    Company code
                                </Label>
                                <Input
                                    id="company_code"
                                    name="company_code"
                                    defaultValue={business.company_code ?? ''}
                                />
                                <InputError message={errors.company_code} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="vat">Company VAT</Label>
                                <Input
                                    id="vat"
                                    name="vat"
                                    defaultValue={business.vat ?? ''}
                                />
                                <InputError message={errors.vat} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="address">
                                    Company address
                                </Label>
                                <textarea
                                    id="address"
                                    name="address"
                                    defaultValue={business.address ?? ''}
                                    rows={4}
                                    className="border-input file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground flex min-h-24 w-full rounded-md border bg-transparent px-3 py-2 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                                />
                                <InputError message={errors.address} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    defaultValue={business.phone ?? ''}
                                    autoComplete="tel"
                                />
                                <InputError message={errors.phone} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="contact_person">
                                    Contact person
                                </Label>
                                <Input
                                    id="contact_person"
                                    name="contact_person"
                                    defaultValue={business.contact_person ?? ''}
                                    autoComplete="name"
                                />
                                <InputError message={errors.contact_person} />
                            </div>

                            <Button disabled={processing}>Save</Button>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

Business.layout = {
    breadcrumbs: [
        {
            title: 'Business settings',
            href: edit(),
        },
    ],
};
